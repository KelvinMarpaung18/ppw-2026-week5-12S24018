<x-layout>
    <x-slot:title>Catat Peminjaman Buku</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('peminjaman.index') }}" style="color: var(--del-purple);">Sirkulasi Peminjaman</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Catat Peminjaman</li>
                    </ol>
                </nav>
                <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card card-custom">
                <div class="card-header card-header-custom">
                    <h5 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-journal-arrow-up me-2" style="color: var(--del-purple);"></i>
                        Formulir Sirkulasi Peminjaman Buku
                    </h5>
                    <p class="text-muted small mb-0">
                        Diproteksi transaksi ACID (<code>DB::transaction()</code>) dan Pessimistic Locking (<code>lockForUpdate()</code>) guna mencegah race condition.
                    </p>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('peminjaman.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- Pilihan Buku -->
                            <div class="col-12">
                                <label for="buku_id" class="form-label fw-semibold">Pilih Buku yang Akan Dipinjam <span class="text-danger">*</span></label>
                                <select class="form-select @error('buku_id') is-invalid @enderror" id="buku_id" name="buku_id" required>
                                    <option value="" disabled {{ old('buku_id', $selectedBukuId) ? '' : 'selected' }}>-- Pilih Buku dari Katalog --</option>
                                    @foreach($bukus as $buku)
                                        <option value="{{ $buku->id }}" 
                                            {{ old('buku_id', $selectedBukuId) == $buku->id ? 'selected' : '' }}
                                            {{ $buku->stok <= 0 ? 'disabled' : '' }}>
                                            {{ $buku->judul }} — ISBN: {{ $buku->isbn }} 
                                            @if($buku->stok > 0)
                                                (Tersedia: {{ $buku->stok }} eks)
                                            @else
                                                (STOK HABIS - Tidak Dapat Dipinjam)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('buku_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Buku dengan stok 0 otomatis tidak dapat dipilih untuk dipinjam.</div>
                            </div>

                            <!-- NIM Peminjam -->
                            <div class="col-md-6">
                                <label for="nim_peminjam" class="form-label fw-semibold">NIM Mahasiswa <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-card-text"></i></span>
                                    <input type="text" 
                                           class="form-control @error('nim_peminjam') is-invalid @enderror" 
                                           id="nim_peminjam" 
                                           name="nim_peminjam" 
                                           value="{{ old('nim_peminjam', session('last_borrower_nim')) }}" 
                                           placeholder="Contoh: 12S24018" 
                                           required>
                                    @error('nim_peminjam')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if(session('last_borrower_nim'))
                                    <div class="form-text text-success small">
                                        <i class="bi bi-clock-history me-1"></i>Diisi otomatis dari histori peminjam terakhir di sesi Anda.
                                    </div>
                                @endif
                            </div>

                            <!-- Nama Peminjam -->
                            <div class="col-md-6">
                                <label for="nama_peminjam" class="form-label fw-semibold">Nama Lengkap Mahasiswa <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" 
                                           class="form-control @error('nama_peminjam') is-invalid @enderror" 
                                           id="nama_peminjam" 
                                           name="nama_peminjam" 
                                           value="{{ old('nama_peminjam', session('last_borrower_name')) }}" 
                                           placeholder="Masukkan nama mahasiswa" 
                                           required>
                                    @error('nama_peminjam')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Durasi Peminjaman -->
                            <div class="col-md-6">
                                <label for="durasi_hari" class="form-label fw-semibold">Durasi Peminjaman (Hari) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-range"></i></span>
                                    <input type="number" 
                                           class="form-control @error('durasi_hari') is-invalid @enderror" 
                                           id="durasi_hari" 
                                           name="durasi_hari" 
                                           min="1" 
                                           max="30" 
                                           value="{{ old('durasi_hari', 7) }}" 
                                           required>
                                    <span class="input-group-text bg-light">Hari</span>
                                    @error('durasi_hari')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Batas standar peminjaman buku perpustakaan kampus adalah 7 hari.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">Tanggal Mulai Pinjam</label>
                                <input type="text" class="form-control bg-light" value="{{ now()->format('d M Y') }} (Hari Ini)" readonly>
                            </div>
                        </div>

                        <!-- Kotak Informasi Aturan ACID -->
                        <div class="alert alert-light border mt-4 mb-0 py-3 small">
                            <h6 class="fw-bold text-dark mb-1">
                                <i class="bi bi-shield-check text-success me-1"></i> Proteksi Transaksi ACID & Integritas Data:
                            </h6>
                            <ul class="mb-0 ps-3 text-muted">
                                <li><strong>Atomicity:</strong> Pengurangan stok dan pencatatan riwayat peminjaman dieksekusi sebagai satu kesatuan.</li>
                                <li><strong>Pessimistic Locking:</strong> Baris data buku dikunci secara eksklusif (<code>lockForUpdate</code>) selama proses.</li>
                                <li><strong>Konsistensi:</strong> Mahasiswa tidak diizinkan meminjam buku yang sama jika peminjaman sebelumnya belum dikembalikan.</li>
                            </ul>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-light border px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-del px-4">
                                <i class="bi bi-check2-circle me-1"></i> Proses Transaksi Peminjaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
