<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BukuApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus (Publik)
     * Menampilkan koleksi buku terpaginasi dengan Filter, Search, Sorting, & Metadata Paginasi
     * Bebas dari N+1 Query Problem via with('kategori') dan withCount('peminjamans')
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['judul', 'penulis', 'tahun_terbit', 'stok', 'created_at'];
        $sortBy = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'created_at';
        $sortDir = strtolower((string) $request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->query('kategori_id'), function ($q, $kategoriId) {
                $q->where('kategori_id', (int) $kategoriId);
            })
            ->when($request->boolean('tersedia'), function ($q) {
                $q->where('stok', '>', 0);
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar koleksi buku berhasil diambil.',
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
     * GET /api/v1/bukus/{id} (Publik)
     * Menampilkan detail satu buku beserta objek kategori dan daftar riwayat peminjamannya
     */
    public function show(string $identifier): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])
            ->withCount('peminjamans')
            ->where('id', $identifier)
            ->orWhere('isbn', $identifier)
            ->first();

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan pengenal '{$identifier}' tidak ditemukan di basis data.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/bukus (Wajib Bearer Token)
     * Membuat buku baru -> Mengembalikan 201 Created + Header Location
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'isbn' => 'required|string|max:30|unique:bukus,isbn',
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|min:1900|max:' . ((int) date('Y') + 1),
            'kategori_id' => 'required|integer|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string|max:2000',
        ]);

        $buku = Buku::create($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Buku baru berhasil dibuat.'
        );
    }

    /**
     * PUT / PATCH /api/v1/bukus/{id} (Wajib Bearer Token)
     * Membuktikan perbedaan nyata antara PUT (Full Replacement) vs PATCH (Partial Update)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            // SEMANTIK PUT: Seluruh field representasi WAJIB dikirimkan secara utuh!
            $validated = $request->validate([
                'isbn' => "required|string|max:30|unique:bukus,isbn,{$buku->id}",
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:150',
                'penerbit' => 'required|string|max:150',
                'tahun_terbit' => 'required|integer|min:1900|max:' . ((int) date('Y') + 1),
                'kategori_id' => 'required|integer|exists:kategoris,id',
                'stok' => 'required|integer|min:0',
                'sinopsis' => 'nullable|string|max:2000',
            ]);

            // Pada PUT murni, jika field nullable tidak dikirim, ia direset ke null
            $validated['sinopsis'] = $request->input('sinopsis', null);
        } else {
            // SEMANTIK PATCH: HANYA memvalidasi & mengubah atribut yang dikirim di body saja!
            $validated = $request->validate([
                'isbn' => "sometimes|required|string|max:30|unique:bukus,isbn,{$buku->id}",
                'judul' => 'sometimes|required|string|max:255',
                'penulis' => 'sometimes|required|string|max:150',
                'penerbit' => 'sometimes|required|string|max:150',
                'tahun_terbit' => 'sometimes|required|integer|min:1900|max:' . ((int) date('Y') + 1),
                'kategori_id' => 'sometimes|required|integer|exists:kategoris,id',
                'stok' => 'sometimes|required|integer|min:0',
                'sinopsis' => 'sometimes|nullable|string|max:2000',
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }
        }

        $buku->update($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi buku berhasil diganti (PUT).'
                : 'Atribut buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    /**
     * DELETE /api/v1/bukus/{id} (Wajib Bearer Token)
     * Menolak penghapusan dengan 409 Conflict jika buku memiliki peminjaman aktif ('dipinjam')
     * Mengembalikan 204 No Content jika berhasil dihapus
     */
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        $activeLoans = Peminjaman::where('buku_id', $buku->id)
            ->where('status', 'dipinjam')
            ->count();

        if ($activeLoans > 0) {
            return $this->problemResponse(
                title: 'Conflict — Book Has Active Loans',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena sedang dipinjam oleh {$activeLoans} mahasiswa.",
                status: 409,
                errorSlug: 'book-has-active-loans'
            );
        }

        $buku->delete();

        return $this->noContentResponse();
    }

    /**
     * GET /api/v1/bukus/{id}/referensi-eksternal (Publik / Konsumen REST API)
     * Memanggil OpenLibrary REST API via fasad Http Laravel dengan batas waktu (timeout) 8 detik
     */
    public function referensiEksternal(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $buku->isbn);
        $bibkey = "ISBN:{$cleanIsbn}";

        try {
            $response = Http::withoutVerifying()
                ->timeout(8)
                ->acceptJson()
                ->get('https://openlibrary.org/api/books', [
                    'bibkeys' => $bibkey,
                    'format' => 'json',
                    'jscmd' => 'data',
                ]);

            if ($response->successful()) {
                $payload = $response->json();
                if (isset($payload[$bibkey])) {
                    $item = $payload[$bibkey];
                    return $this->successResponse([
                        'buku_id' => $buku->id,
                        'isbn' => $buku->isbn,
                        'judul_lokal' => $buku->judul,
                        'sumber' => 'OpenLibrary REST API',
                        'metadata_eksternal' => [
                            'title' => $item['title'] ?? null,
                            'publishers' => collect($item['publishers'] ?? [])->pluck('name')->toArray(),
                            'publish_date' => $item['publish_date'] ?? null,
                            'number_of_pages' => $item['number_of_pages'] ?? null,
                            'url' => $item['url'] ?? null,
                            'cover_url' => $item['cover']['medium'] ?? $item['cover']['small'] ?? null,
                        ],
                    ], 'Data referensi buku eksternal berhasil diambil dari OpenLibrary API.');
                }
            }

            return $this->successResponse([
                'buku_id' => $buku->id,
                'isbn' => $buku->isbn,
                'judul_lokal' => $buku->judul,
                'sumber' => 'OpenLibrary REST API',
                'metadata_eksternal' => null,
                'catatan' => 'Metadata buku untuk ISBN ini belum tersedia di katalog OpenLibrary.',
            ], 'Pencarian referensi eksternal selesai.');
        } catch (\Throwable $e) {
            return $this->problemResponse(
                title: 'External Service Unavailable',
                detail: 'Gagal berkomunikasi dengan layanan OpenLibrary API: ' . $e->getMessage(),
                status: 502,
                errorSlug: 'external-service-error'
            );
        }
    }
}
