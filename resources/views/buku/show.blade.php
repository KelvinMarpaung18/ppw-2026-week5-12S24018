<x-layout>
    <x-slot:title>Detail Buku — {{ $buku->judul }}</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('buku.index') }}" style="color: var(--del-purple);">Koleksi Buku</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Buku</li>
                    </ol>
                </nav>
                <a href="{{ route('buku.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

            <div class="card card-custom">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge badge-del mb-2">
                            <i class="bi bi-tag-fill me-1"></i>{{ $buku->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                        </span>
                        <h4 class="fw-bold mb-0 text-dark">{{ $buku->judul }}</h4>
                    </div>
                    <div>
                        @if($buku->stok > 5)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                                <i class="bi bi-check-circle me-1"></i>Stok: {{ $buku->stok }} eks
                            </span>
                        @elseif($buku->stok > 0)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 fs-6">
                                <i class="bi bi-exclamation-circle me-1"></i>Stok: {{ $buku->stok }} eks (Terbatas)
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6">
                                <i class="bi bi-x-circle me-1"></i>Stok Habis
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small mb-1"><i class="bi bi-upc-scan me-1"></i> Nomor ISBN</div>
                                <div class="fw-bold font-monospace fs-6">{{ $buku->isbn }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small mb-1"><i class="bi bi-folder-check me-1"></i> Kode Kategori</div>
                                <div class="fw-bold fs-6">{{ $buku->kategori->kode_kategori ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small mb-1"><i class="bi bi-person-badge me-1"></i> Penulis</div>
                            <div class="fw-semibold text-dark">{{ $buku->penulis }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small mb-1"><i class="bi bi-building me-1"></i> Penerbit</div>
                            <div class="fw-semibold text-dark">{{ $buku->penerbit }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small mb-1"><i class="bi bi-calendar3 me-1"></i> Tahun Terbit</div>
                            <div class="fw-semibold text-dark">{{ $buku->tahun_terbit }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <i class="bi bi-file-earmark-text me-1" style="color: var(--del-purple);"></i>
                            Sinopsis / Deskripsi Buku
                        </h6>
                        <div class="p-3 bg-light rounded-3">
                            <p class="mb-0 text-secondary" style="line-height: 1.8; text-align: justify;">
                                {{ $buku->sinopsis ?: 'Tidak ada sinopsis atau deskripsi untuk buku ini.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Riwayat Transaksi Peminjaman untuk Buku Ini -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="bi bi-clock-history me-1" style="color: var(--del-purple);"></i>
                                Riwayat Sirkulasi Peminjaman Buku Ini ({{ $buku->peminjamans->count() }} Transaksi)
                            </h6>
                            @auth
                                @if($buku->stok > 0)
                                    <a href="{{ route('peminjaman.create', ['buku_id' => $buku->id]) }}" class="btn btn-sm btn-warning text-dark fw-semibold">
                                        <i class="bi bi-arrow-left-right me-1"></i> Pinjamkan Buku Ini
                                    </a>
                                @else
                                    <span class="badge bg-danger">Stok Habis (Tidak Dapat Dipinjam)</span>
                                @endif
                            @endauth
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Pinjam</th>
                                        <th>Peminjam (Mahasiswa)</th>
                                        <th>Tgl Pinjam</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Tgl Kembali</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($buku->peminjamans as $pinjam)
                                        <tr>
                                            <td class="font-monospace small fw-semibold">{{ $pinjam->kode_pinjam }}</td>
                                            <td>
                                                <div>{{ $pinjam->nama_peminjam }}</div>
                                                <small class="text-muted font-monospace">{{ $pinjam->nim_peminjam }}</small>
                                            </td>
                                            <td class="small">{{ $pinjam->tanggal_pinjam->format('d/m/Y') }}</td>
                                            <td class="small">{{ $pinjam->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                                            <td class="small">
                                                {{ $pinjam->tanggal_kembali ? $pinjam->tanggal_kembali->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="text-center">
                                                @if($pinjam->status === 'dipinjam')
                                                    <span class="badge bg-warning text-dark">Dipinjam</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border">Kembali</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3 small">
                                                Belum ada riwayat peminjaman untuk buku ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <div class="text-muted small">
                            <i class="bi bi-clock me-1"></i> Terdaftar di sistem: {{ $buku->created_at->format('d M Y, H:i') }} WIB
                        </div>
                        <div class="d-flex gap-2">
                            @auth
                                <a href="{{ route('buku.edit', $buku) }}" class="btn btn-warning btn-sm px-3 text-dark fw-medium">
                                    <i class="bi bi-pencil me-1"></i> Edit Buku
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#deleteModalShow">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-del btn-sm px-3">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Login untuk Meminjam
                                </a>
                            @endauth
                        </div>
                    </div>

                    @auth
                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="deleteModalShow" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title text-danger">
                                            <i class="bi bi-exclamation-octagon me-2"></i>Konfirmasi Hapus
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus buku <strong>"{{ $buku->judul }}"</strong>?
                                        <div class="alert alert-warning mt-2 mb-0 py-2 small">
                                            Tindakan ini tidak dapat dibatalkan.
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
                    @endauth

                </div>
            </div>
        </div>
    </div>
</x-layout>
