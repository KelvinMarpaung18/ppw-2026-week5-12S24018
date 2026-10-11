<x-layout>
    <x-slot:title>Detail Kategori — {{ $kategori->nama_kategori }}</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}" style="color: var(--del-purple);">Kategori Buku</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Kategori</li>
                    </ol>
                </nav>
                <a href="{{ route('kategori.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Kategori
                </a>
            </div>

            <!-- Kartu Info Kategori -->
            <div class="card card-custom mb-4">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge badge-del font-monospace fs-6 mb-2">{{ $kategori->kode_kategori }}</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ $kategori->nama_kategori }}</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-warning btn-sm text-dark fw-medium">
                            <i class="bi bi-pencil me-1"></i> Edit Kategori
                        </a>
                        <a href="{{ route('buku.create') }}" class="btn btn-del btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Buku Baru
                        </a>
                    </div>
                </div>
            </div>

            <!-- Koleksi Buku dalam Kategori Ini -->
            <div class="card card-custom">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-journal-bookmark me-2" style="color: var(--del-purple);"></i>
                        Koleksi Buku di Kategori Ini
                    </h5>
                    <span class="badge bg-light text-dark border">
                        {{ $kategori->bukus->count() }} Buku Ditemukan
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th style="width: 150px;">ISBN</th>
                                    <th>Judul Buku</th>
                                    <th>Penulis & Penerbit</th>
                                    <th class="text-center" style="width: 100px;">Stok</th>
                                    <th class="text-center" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategori->bukus as $index => $buku)
                                    <tr>
                                        <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                        <td>
                                            <span class="font-monospace small">{{ $buku->isbn }}</span>
                                            <div class="text-muted small">Tahun {{ $buku->tahun_terbit }}</div>
                                        </td>
                                        <td>
                                            <a href="{{ route('buku.show', $buku) }}" class="fw-bold text-decoration-none" style="color: var(--del-purple);">
                                                {{ $buku->judul }}
                                            </a>
                                            @if($buku->sinopsis)
                                                <p class="text-muted small mb-0 text-truncate" style="max-width: 320px;">
                                                    {{ Str::limit($buku->sinopsis, 75) }}
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $buku->penulis }}</div>
                                            <div class="text-muted small">{{ $buku->penerbit }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($buku->stok > 5)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    {{ $buku->stok }} eks
                                                </span>
                                            @elseif($buku->stok > 0)
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                                    {{ $buku->stok }} eks
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                    Habis
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('buku.show', $buku) }}" class="btn btn-outline-info" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-warning" title="Edit Buku">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                                                <h6 class="fw-bold mb-1">Belum Ada Buku di Kategori Ini</h6>
                                                <p class="small mb-3">Tambahkan buku baru dan pilih kategori ini pada formulir.</p>
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
            </div>
        </div>
    </div>
</x-layout>
