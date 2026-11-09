<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans (Wajib Bearer Token)
     * Menampilkan daftar transaksi peminjaman terpaginasi dengan filter status dan NIM
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku.kategori')
            ->when($request->query('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->query('nim'), function ($q, $nim) {
                $q->where('nim_peminjam', $nim);
            })
            ->when($request->query('nim_peminjam'), function ($q, $nim) {
                $q->where('nim_peminjam', $nim);
            })
            ->when($request->query('buku_id'), function ($q, $bukuId) {
                $q->where('buku_id', (int) $bukuId);
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar transaksi peminjaman berhasil diambil.',
            status: 200,
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/peminjamans/{id} (Wajib Bearer Token)
     * Menampilkan detail satu transaksi peminjaman
     */
    public function show(string $id): JsonResponse
    {
        $peminjaman = Peminjaman::with('buku.kategori')->find($id);

        if (!$peminjaman) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'peminjaman-not-found'
            );
        }

        return $this->successResponse(
            data: new PeminjamanResource($peminjaman),
            message: 'Detail peminjaman berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/peminjamans (Wajib Bearer Token)
     * Mencatat peminjaman buku baru secara atomik dengan jaminan ACID + Pessimistic Locking
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|integer|exists:bukus,id',
            'nim_peminjam' => 'required|string|min:6|max:20',
            'nama_peminjam' => 'required|string|min:3|max:150',
            'durasi_hari' => 'required|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Kunci baris buku secara eksklusif (SELECT ... FOR UPDATE) untuk menangkal Race Condition
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            if (!$buku) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: 'Buku yang dipilih tidak ditemukan dalam katalog.',
                    status: 404,
                    errorSlug: 'buku-not-found'
                );
            }

            $nim = strtoupper(trim($validated['nim_peminjam']));

            // 2. Mencegah mahasiswa meminjam buku yang sama jika belum dikembalikan -> 409 Conflict
            $alreadyBorrowed = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $nim)
                ->where('status', 'dipinjam')
                ->lockForUpdate()
                ->first();

            if ($alreadyBorrowed) {
                return $this->problemResponse(
                    title: 'Duplicate Loan Conflict',
                    detail: "Mahasiswa dengan NIM '{$nim}' masih memiliki pinjaman aktif untuk buku '{$buku->judul}' (Kode: {$alreadyBorrowed->kode_pinjam}).",
                    status: 409,
                    errorSlug: 'duplicate-loan'
                );
            }

            // 3. Cek ketersediaan sisa stok buku -> 422 Unprocessable Content jika stok habis (< 1)
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Book Out of Stock',
                    detail: "Stok buku '{$buku->judul}' sedang habis (0 tersisa).",
                    status: 422,
                    errorSlug: 'quota-exhausted'
                );
            }

            // 4. Kurangi stok (-1) secara atomik
            $buku->decrement('stok');

            // 5. Catat transaksi peminjaman baru
            $durasi = (int) $validated['durasi_hari'];
            $kodePinjam = 'PINJAM-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $peminjaman = Peminjaman::create([
                'kode_pinjam' => $kodePinjam,
                'buku_id' => $buku->id,
                'nim_peminjam' => $nim,
                'nama_peminjam' => trim($validated['nama_peminjam']),
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays($durasi)->toDateString(),
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
            ]);

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman->load('buku')),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman buku '{$buku->judul}' berhasil dicatat. Sisa stok kini: {$buku->fresh()->stok}."
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan (Wajib Bearer Token)
     * Mengembalikan buku di dalam DB::transaction() + lockForUpdate()
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::find($id);

            if (!$peminjaman) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'peminjaman-not-found'
                );
            }

            // Tolak dengan 409 Conflict jika buku sudah pernah dikembalikan sebelumnya
            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Conflict — Loan Already Returned',
                    detail: "Buku pada transaksi peminjaman '{$peminjaman->kode_pinjam}' sudah pernah dikembalikan sebelumnya pada tanggal {$peminjaman->tanggal_kembali}.",
                    status: 409,
                    errorSlug: 'loan-already-returned'
                );
            }

            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            if ($buku) {
                $buku->increment('stok');
            }

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman->fresh()->load('buku')),
                message: "Buku '{$buku?->judul}' berhasil dikembalikan. Stok buku bertambah menjadi {$buku?->fresh()->stok}."
            );
        });
    }

    /**
     * DELETE /api/v1/peminjamans/{id} (Wajib Bearer Token)
     * Membatalkan / menghapus data peminjaman
     */
    public function destroy(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::find($id);

            if (!$peminjaman) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'peminjaman-not-found'
                );
            }

            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            // Jika peminjaman aktif dibatalkan, kembalikan stok buku
            if ($peminjaman->status === 'dipinjam' && $buku) {
                $buku->increment('stok');
            }

            $peminjaman->delete();

            return $this->noContentResponse();
        });
    }
}
