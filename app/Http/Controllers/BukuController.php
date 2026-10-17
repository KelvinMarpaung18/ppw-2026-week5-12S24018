<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BukuController extends Controller
{
    /**
     * Tampilkan katalog buku dengan Eager Loading, withCount, filter dinamis when(),
     * dan kartu statistik perpustakaan menggunakan Fluent Query Builder (DB::table).
     */
    public function index(Request $request): View
    {
        // Simpan riwayat pencarian terakhir ke sesi pengguna (Session State)
        if ($request->filled('search')) {
            session(['last_buku_search' => $request->search]);
        }

        // Kueri Eloquent dengan Eager Loading relasi kategori dan hitung jumlah peminjaman (withCount)
        $bukus = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%")
                        ->orWhere('penerbit', 'like', "%{$search}%");
                });
            })
            ->when($request->kategori_id, function ($q, $kategoriId) {
                $q->where('kategori_id', $kategoriId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Kartu statistik perpustakaan menggunakan Fluent Query Builder (DB::table) sesuai spesifikasi Modul Minggu 6
        $stats = [
            'total_judul' => DB::table('bukus')->count(),
            'total_stok' => (int) DB::table('bukus')->sum('stok'),
            'total_dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'total_dikembalikan' => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.index', compact('bukus', 'kategoris', 'stats'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create(): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.create', compact('kategoris'));
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentYear = (int) date('Y');

        $validated = $request->validate([
            'isbn' => ['required', 'string', 'max:30', 'unique:bukus,isbn'],
            'judul' => ['required', 'string', 'min:5', 'max:255'],
            'penulis' => ['required', 'string', 'min:3', 'max:150'],
            'penerbit' => ['required', 'string', 'min:3', 'max:150'],
            'tahun_terbit' => ['required', 'integer', 'digits:4', 'min:1900', 'max:' . ($currentYear + 1)],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
            'sinopsis' => ['nullable', 'string'],
        ], [
            'isbn.required' => 'Nomor ISBN wajib diisi.',
            'isbn.unique' => 'Nomor ISBN sudah terdaftar di sistem.',
            'judul.required' => 'Judul buku wajib diisi.',
            'judul.min' => 'Judul buku minimal harus memiliki 5 karakter.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'penerbit.required' => 'Nama penerbit wajib diisi.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.digits' => 'Tahun terbit harus berupa 4 digit tahun (contoh: 2024).',
            'kategori_id.required' => 'Kategori buku wajib dipilih.',
            'kategori_id.exists' => 'Kategori buku yang dipilih tidak valid.',
            'stok.required' => 'Jumlah stok buku wajib diisi.',
            'stok.integer' => 'Stok buku harus berupa bilangan bulat.',
            'stok.min' => 'Jumlah stok minimal 0.',
        ]);

        Buku::create($validated);

        return redirect()
            ->route('buku.index')
            ->with('success', 'Buku baru berhasil ditambahkan ke koleksi perpustakaan!');
    }

    /**
     * Display the specified book details.
     */
    public function show(Buku $buku): View
    {
        $buku->load(['kategori', 'peminjamans' => function ($q) {
            $q->latest();
        }]);

        return view('buku.show', compact('buku'));
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Buku $buku): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.edit', compact('buku', 'kategoris'));
    }

    /**
     * Update the specified book in storage.
     */
    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $currentYear = (int) date('Y');

        $validated = $request->validate([
            'isbn' => [
                'required',
                'string',
                'max:30',
                Rule::unique('bukus', 'isbn')->ignore($buku->id),
            ],
            'judul' => ['required', 'string', 'min:5', 'max:255'],
            'penulis' => ['required', 'string', 'min:3', 'max:150'],
            'penerbit' => ['required', 'string', 'min:3', 'max:150'],
            'tahun_terbit' => ['required', 'integer', 'digits:4', 'min:1900', 'max:' . ($currentYear + 1)],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
            'sinopsis' => ['nullable', 'string'],
        ], [
            'isbn.required' => 'Nomor ISBN wajib diisi.',
            'isbn.unique' => 'Nomor ISBN sudah digunakan oleh buku lain.',
            'judul.required' => 'Judul buku wajib diisi.',
            'judul.min' => 'Judul buku minimal harus memiliki 5 karakter.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'penerbit.required' => 'Nama penerbit wajib diisi.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.digits' => 'Tahun terbit harus berupa 4 digit tahun (contoh: 2024).',
            'kategori_id.required' => 'Kategori buku wajib dipilih.',
            'kategori_id.exists' => 'Kategori buku yang dipilih tidak valid.',
            'stok.required' => 'Jumlah stok buku wajib diisi.',
            'stok.integer' => 'Stok buku harus berupa bilangan bulat.',
            'stok.min' => 'Jumlah stok minimal 0.',
        ]);

        $buku->update($validated);

        return redirect()
            ->route('buku.index')
            ->with('success', 'Data buku berhasil diperbarui!');
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Buku $buku): RedirectResponse
    {
        $judul = $buku->judul;
        $buku->delete();

        return redirect()
            ->route('buku.index')
            ->with('success', "Buku '{$judul}' berhasil dihapus dari sistem perpustakaan!");
    }
}
