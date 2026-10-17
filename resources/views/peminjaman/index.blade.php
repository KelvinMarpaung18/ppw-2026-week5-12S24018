<x-layout>
    <x-slot:title>Sirkulasi Peminjaman Buku</x-slot:title>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-arrow-left-right text-primary me-2" style="color: var(--del-purple) !important;"></i>
                Sirkulasi Peminjaman Buku (ACID Transaction)
            </h1>
            <p class="text-muted mb-0">Kelola peminjaman dan pengembalian buku dengan proteksi transaksi atomik dan Pessimistic Locking.</p>
        </div>
        <div>
            <a href="{{ route('peminjaman.create') }}" class="btn btn-warning text-dark fw-semibold d-inline-flex align-items-center shadow-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> Catat Peminjaman Baru
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian Peminjaman -->
    <div class="card card-custom mb-4">
        <div class="card-body p-3">
            <form action="{{ route('peminjaman.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari Kode Pinjam, NIM, Nama Mahasiswa, Judul Buku..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                        <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-del flex-grow-1">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-light border text-muted" title="Reset Filter">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Peminjaman -->
    <div class="card card-custom">
        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 text-dark">
                Daftar Riwayat Sirkulasi Peminjaman
            </h5>
            <span class="badge bg-light text-dark border">
                Total: {{ $peminjamans->total() }} Transaksi
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 170px;">Kode Pinjam</th>
                            <th>Buku Dipinjam</th>
                            <th>Peminjam (Mahasiswa)</th>
                            <th>Tgl Pinjam & Tempo</th>
                            <th class="text-center" style="width: 130px;">Status</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjamans as $index => $pinjam)
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ $peminjamans->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace fs-6">
                                        {{ $pinjam->kode_pinjam }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('buku.show', $pinjam->buku) }}" class="fw-bold text-decoration-none" style="color: var(--del-purple);">
                                        {{ $pinjam->buku->judul }}
                                    </a>
                                    <div class="text-muted small">
                                        ISBN: {{ $pinjam->buku->isbn }} &bull; Sisa Stok: {{ $pinjam->buku->stok }} eks
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $pinjam->nama_peminjam }}</div>
                                    <div class="text-muted small font-monospace">NIM: {{ $pinjam->nim_peminjam }}</div>
                                </td>
                                <td>
                                    <div class="small">
                                        <i class="bi bi-calendar-check me-1 text-primary"></i>Pinjam: {{ $pinjam->tanggal_pinjam->format('d M Y') }}
                                    </div>
                                    <div class="small {{ $pinjam->status === 'dipinjam' && $pinjam->tanggal_jatuh_tempo->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                        <i class="bi bi-calendar-x me-1"></i>Tempo: {{ $pinjam->tanggal_jatuh_tempo->format('d M Y') }}
                                        @if($pinjam->status === 'dipinjam' && $pinjam->tanggal_jatuh_tempo->isPast())
                                            <span class="badge bg-danger ms-1">Terlambat</span>
                                        @endif
                                    </div>
                                    @if($pinjam->tanggal_kembali)
                                        <div class="small text-success">
                                            <i class="bi bi-check-circle me-1"></i>Kembali: {{ $pinjam->tanggal_kembali->format('d M Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($pinjam->status === 'dipinjam')
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bi bi-hourglass-split me-1"></i>Sedang Dipinjam
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>Dikembalikan
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($pinjam->status === 'dipinjam')
                                        <button type="button" class="btn btn-sm btn-success px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#returnModal{{ $pinjam->id }}">
                                            <i class="bi bi-box-arrow-in-down-left me-1"></i> Kembalikan
                                        </button>

                                        <!-- Modal Konfirmasi Pengembalian Buku -->
                                        <div class="modal fade" id="returnModal{{ $pinjam->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-success">
                                                            <i class="bi bi-check2-circle me-2"></i>Konfirmasi Pengembalian Buku
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-2">Apakah mahasiswa <strong>{{ $pinjam->nama_peminjam }} (NIM: {{ $pinjam->nim_peminjam }})</strong> mengembalikan buku:</p>
                                                        <div class="p-3 bg-light rounded-3 mb-2">
                                                            <strong>"{{ $pinjam->buku->judul }}"</strong><br>
                                                            <small class="text-muted">Kode Transaksi: {{ $pinjam->kode_pinjam }}</small>
                                                        </div>
                                                        <div class="alert alert-info py-2 px-3 small mb-0">
                                                            <i class="bi bi-info-circle me-1"></i>Stok buku akan otomatis bertambah (+1) secara aman via transaksi ACID.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <form action="{{ route('peminjaman.kembalikan', $pinjam) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-success btn-sm px-3 fw-semibold">
                                                                <i class="bi bi-check-lg me-1"></i> Ya, Proses Pengembalian
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">
                                            <i class="bi bi-check2-all text-success"></i> Selesai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-journal-check fs-1 d-block mb-2 text-secondary"></i>
                                        <h6 class="fw-bold mb-1">Belum Ada Riwayat Peminjaman</h6>
                                        <p class="small mb-3">Seluruh sirkulasi peminjaman buku perpustakaan akan dicatat dan diaudit di sini.</p>
                                        <a href="{{ route('peminjaman.create') }}" class="btn btn-sm btn-del">
                                            <i class="bi bi-plus-circle me-1"></i> Catat Peminjaman Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($peminjamans->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center pt-3">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
</x-layout>
