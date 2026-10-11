<x-layout>
    <x-slot:title>Daftar Koleksi Buku</x-slot:title>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-journal-album text-primary me-2" style="color: var(--del-purple) !important;"></i>
                Koleksi Buku Perpustakaan
            </h1>
            <p class="text-muted mb-0">Kelola dan telusuri seluruh daftar inventaris buku kampus IT Del.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('buku.create') }}" class="btn btn-del d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> Tambah Buku Baru
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card card-custom mb-4">
        <div class="card-body p-3">
            <form action="{{ route('buku.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari judul, ISBN, penulis, penerbit..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori_id" class="form-select">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                [{{ $kategori->kode_kategori }}] {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-del flex-grow-1">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    @if(request('search') || request('kategori_id'))
                        <a href="{{ route('buku.index') }}" class="btn btn-light border text-muted" title="Reset Filter">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Buku -->
    <div class="card card-custom">
        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 text-dark">
                Daftar Inventaris Buku
            </h5>
            <span class="badge bg-light text-dark border">
                Total: {{ $bukus->total() }} Buku
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 140px;">ISBN</th>
                            <th>Judul & Sinopsis</th>
                            <th>Penulis & Penerbit</th>
                            <th>Kategori</th>
                            <th class="text-center" style="width: 100px;">Stok</th>
                            <th class="text-center" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bukus as $index => $buku)
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ $bukus->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="font-monospace text-dark fw-medium small">{{ $buku->isbn }}</span>
                                    <div class="text-muted small">Tahun: {{ $buku->tahun_terbit }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('buku.show', $buku) }}" class="fw-bold text-decoration-none" style="color: var(--del-purple);">
                                        {{ $buku->judul }}
                                    </a>
                                    @if($buku->sinopsis)
                                        <p class="text-muted small mb-0 text-truncate" style="max-width: 380px;">
                                            {{ Str::limit($buku->sinopsis, 90) }}
                                        </p>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $buku->penulis }}</div>
                                    <div class="text-muted small">{{ $buku->penerbit }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-del">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $buku->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($buku->stok > 5)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i>{{ $buku->stok }} eks
                                        </span>
                                    @elseif($buku->stok > 0)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $buku->stok }} eks
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            <i class="bi bi-x-circle me-1"></i>Habis
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('buku.show', $buku) }}" class="btn btn-outline-info" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-warning" title="Edit Data">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $buku->id }}" title="Hapus Buku">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal Konfirmasi Hapus -->
                                    <div class="modal fade" id="deleteModal{{ $buku->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger">
                                                        <i class="bi bi-exclamation-octagon me-2"></i>Konfirmasi Hapus
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Apakah Anda yakin ingin menghapus buku berjudul <strong>"{{ $buku->judul }}"</strong> (ISBN: {{ $buku->isbn }})?
                                                    <div class="alert alert-warning mt-2 mb-0 py-2 small">
                                                        Tindakan ini permanen dan tidak dapat dibatalkan.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline">
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                                        <h6 class="fw-bold mb-1">Belum Ada Data Buku</h6>
                                        <p class="small mb-3">Tidak ditemukan koleksi buku sesuai filter atau inventaris masih kosong.</p>
                                        <a href="{{ route('buku.create') }}" class="btn btn-sm btn-del">
                                            <i class="bi bi-plus-lg me-1"></i> Tambah Buku Sekarang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($bukus->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center pt-3">
                {{ $bukus->links() }}
            </div>
        @endif
    </div>
</x-layout>
