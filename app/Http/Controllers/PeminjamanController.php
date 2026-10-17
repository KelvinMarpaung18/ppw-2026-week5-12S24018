<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Tampilkan riwayat dan daftar transaksi peminjaman buku.
     */
    public function index(Request $request): View
    {
        $query = Peminjaman::with('buku.kategori');

        // Filter berdasarkan status peminjaman ('dipinjam' / 'dikembalikan')
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pencarian dinamis berdasarkan Kode Pinjam, Judul Buku, NIM, atau Nama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_pinjam', 'like', "%{$search}%")
                  ->orWhere('nim_peminjam', 'like', "%{$search}%")
                  ->orWhere('nama_peminjam', 'like', "%{$search}%")
                  ->orWhereHas('buku', function ($sub) use ($search) {
                      $sub->where('judul', 'like', "%{$search}%")
                          ->orWhere('isbn', 'like', "%{$search}%");
                  });
            });
        }

        $peminjamans = $query->latest()->paginate(10)->withQueryString();

        return view('peminjaman.index', compact('peminjamans'));
    }

    /**
     * Tampilkan formulir pencatatan peminjaman buku baru.
     */
    public function create(Request $request): View
    {
        // Ambil daftar buku untuk opsi pilihan
        $bukus = Buku::orderBy('judul')->get();

        // Ambil ID buku jika dipilih langsung dari katalog
        $selectedBukuId = $request->query('buku_id');

        return view('peminjaman.create', compact('bukus', 'selectedBukuId'));
    }

    /**
     * Simpan transaksi peminjaman buku berbasis ACID (DB::transaction & lockForUpdate).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buku_id' => ['required', 'exists:bukus,id'],
            'nim_peminjam' => ['required', 'string', 'min:6', 'max:20'],
            'nama_peminjam' => ['required', 'string', 'min:3', 'max:150'],
            'durasi_hari' => ['required', 'integer', 'min:1', 'max:30'],
        ], [
            'buku_id.required' => 'Buku yang akan dipinjam wajib dipilih.',
            'buku_id.exists' => 'Buku yang dipilih tidak ditemukan dalam katalog.',
            'nim_peminjam.required' => 'NIM peminjam wajib diisi.',
            'nim_peminjam.min' => 'NIM peminjam minimal 6 karakter.',
            'nama_peminjam.required' => 'Nama peminjam wajib diisi.',
            'durasi_hari.required' => 'Durasi peminjaman wajib ditentukan.',
            'durasi_hari.min' => 'Durasi peminjaman minimal 1 hari.',
            'durasi_hari.max' => 'Durasi peminjaman maksimal 30 hari.',
        ]);

        try {
            // Bungkus seluruh operasi multi-tabel dalam DB::transaction untuk menjamin sifat Atomicity
            $peminjaman = DB::transaction(function () use ($validated) {
                // 1. Kunci baris buku menggunakan Pessimistic Locking (lockForUpdate)
                // Mencegah Race Condition stok minus jika dua pengguna meminjam buku yang sama di milidetik yang sama
                $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->firstOrFail();

                // 2. Pemeriksaan Stok > 0 (Tolak & Rollback jika stok = 0)
                if ($buku->stok < 1) {
                    throw new \RuntimeException("Transaksi Ditolak: Stok buku '{$buku->judul}' habis (0) dan tidak tersedia untuk dipinjam!");
                }

                // 3. Mencegah NIM yang sama meminjam buku yang sama yang belum dikembalikan
                $nim = strtoupper(trim($validated['nim_peminjam']));
                $peminjamanAktif = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $nim)
                    ->where('status', 'dipinjam')
                    ->lockForUpdate()
                    ->first();

                if ($peminjamanAktif) {
                    throw new \RuntimeException("Transaksi Ditolak: Mahasiswa dengan NIM {$nim} masih memiliki pinjaman aktif untuk buku ini (Kode: {$peminjamanAktif->kode_pinjam})!");
                }

                // 4. Mutasi Stok Atomik: Kurangi stok buku sebesar 1
                $buku->decrement('stok');

                // 5. Catat transaksi ke tabel peminjamans
                $tanggalPinjam = now();
                $durasi = (int) $validated['durasi_hari'];
                $tanggalJatuhTempo = now()->addDays($durasi);
                $kodePinjam = 'PINJAM-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                $baru = Peminjaman::create([
                    'kode_pinjam' => $kodePinjam,
                    'buku_id' => $buku->id,
                    'nim_peminjam' => $nim,
                    'nama_peminjam' => trim($validated['nama_peminjam']),
                    'tanggal_pinjam' => $tanggalPinjam->toDateString(),
                    'tanggal_jatuh_tempo' => $tanggalJatuhTempo->toDateString(),
                    'tanggal_kembali' => null,
                    'status' => 'dipinjam',
                ]);

                // Simpan state sesi peminjam terakhir (sesuai spesifikasi modul)
                session([
                    'last_borrower_nim' => $nim,
                    'last_borrower_name' => trim($validated['nama_peminjam']),
                ]);

                return $baru;
            }, 3);

            return redirect()
                ->route('peminjaman.index')
                ->with('success', "Peminjaman buku berhasil dicatat secara ACID! Kode Transaksi: {$peminjaman->kode_pinjam}");
        } catch (\RuntimeException $e) {
            Log::warning('[PEMINJAMAN REJECTED]: ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[PEMINJAMAN DB EXCEPTION]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan basis data saat memproses peminjaman.');
        }
    }

    /**
     * Proses pengembalian buku berbasis ACID (DB::transaction & lockForUpdate).
     */
    public function kembalikan(Peminjaman $peminjaman): RedirectResponse
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                // 1. Kunci baris peminjaman dengan lockForUpdate
                $pinjam = Peminjaman::where('id', $peminjaman->id)->lockForUpdate()->firstOrFail();

                if ($pinjam->status === 'dikembalikan') {
                    throw new \RuntimeException("Buku pada transaksi {$pinjam->kode_pinjam} sudah dikembalikan sebelumnya!");
                }

                // 2. Kunci baris buku dengan lockForUpdate
                $buku = Buku::where('id', $pinjam->buku_id)->lockForUpdate()->firstOrFail();

                // 3. Ubah status menjadi 'dikembalikan' dan isi tanggal_kembali
                $pinjam->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // 4. Kembalikan stok buku (+1) secara atomik
                $buku->increment('stok');
            }, 3);

            return redirect()
                ->route('peminjaman.index')
                ->with('success', "Pengembalian buku untuk transaksi {$peminjaman->kode_pinjam} berhasil diproses! Stok buku berhasil dipulihkan.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[PENGEMBALIAN DB EXCEPTION]: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses pengembalian buku.');
        }
    }
}
