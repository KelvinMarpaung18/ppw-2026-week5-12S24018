<x-layout>
    <x-slot:title>Daftar Kategori Buku</x-slot:title>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-tags text-primary me-2" style="color: var(--del-purple) !important;"></i>
                Kategori Buku
            </h1>
            <p class="text-muted mb-0">Kelola klasifikasi dan taksonomi kategori perpustakaan kampus IT Del.</p>
        </div>
        <div>
            <a href="{{ route('kategori.create') }}" class="btn btn-del d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kategori Baru
            </a>
        </div>
    </div>

    <!-- Tabel Data Kategori -->
    <div class="card card-custom">
        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 text-dark">
                Daftar Kategori Terdaftar
            </h5>
            <span class="badge bg-light text-dark border">
                Total: {{ $kategoris->total() }} Kategori
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th style="width: 180px;">Kode Kategori</th>
                            <th>Nama Kategori</th>
                            <th class="text-center" style="width: 150px;">Jumlah Koleksi</th>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoris as $index => $kategori)
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ $kategoris->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="badge badge-del font-monospace fs-6">
                                        {{ $kategori->kode_kategori }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('kategori.show', $kategori) }}" class="fw-bold text-decoration-none" style="color: var(--del-purple);">
                                        {{ $kategori->nama_kategori }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('buku.index', ['kategori_id' => $kategori->id]) }}" class="badge bg-info-subtle text-info-emphasis border border-info-subtle text-decoration-none px-3 py-2" title="Lihat buku di kategori ini">
                                        <i class="bi bi-book me-1"></i> {{ $kategori->bukus_count }} Judul
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('kategori.show', $kategori) }}" class="btn btn-outline-info" title="Lihat Koleksi Buku">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-outline-warning" title="Edit Kategori">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModalKat{{ $kategori->id }}" title="Hapus Kategori">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal Konfirmasi Hapus Kategori -->
                                    <div class="modal fade" id="deleteModalKat{{ $kategori->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger">
                                                        <i class="bi bi-exclamation-octagon me-2"></i>Konfirmasi Hapus Kategori
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Apakah Anda yakin ingin menghapus kategori <strong>"{{ $kategori->nama_kategori }}"</strong> ({{ $kategori->kode_kategori }})?
                                                    <div class="alert alert-danger mt-3 mb-0 py-2 small">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                        <strong>Perhatian:</strong> Menghapus kategori ini juga akan menghapus <strong>{{ $kategori->bukus_count }}</strong> buku yang terikat karena aturan <em>Cascade On Delete</em>.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="bi bi-trash-fill me-1"></i> Ya, Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-tags fs-1 d-block mb-2 text-secondary"></i>
                                        <h6 class="fw-bold mb-1">Belum Ada Kategori Buku</h6>
                                        <p class="small mb-3">Silakan tambahkan kategori untuk mulai mengelompokkan koleksi buku perpustakaan.</p>
                                        <a href="{{ route('kategori.create') }}" class="btn btn-sm btn-del">
                                            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($kategoris->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center pt-3">
                {{ $kategoris->links() }}
            </div>
        @endif
    </div>
</x-layout>
