<x-layout>
    <x-slot:title>Edit Buku — {{ $buku->judul }}</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('buku.index') }}" style="color: var(--del-purple);">Koleksi Buku</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('buku.show', $buku) }}" style="color: var(--del-purple);">{{ Str::limit($buku->judul, 25) }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
                <a href="{{ route('buku.show', $buku) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card card-custom">
                <div class="card-header card-header-custom">
                    <h5 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-pencil-square me-2" style="color: var(--del-purple);"></i>
                        Formulir Perubahan Data Buku
                    </h5>
                    <p class="text-muted small mb-0">Perbarui informasi katalog buku dengan data yang akurat.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('buku.update', $buku) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- ISBN -->
                            <div class="col-md-6">
                                <label for="isbn" class="form-label fw-semibold">Nomor ISBN <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" 
                                           class="form-control @error('isbn') is-invalid @enderror" 
                                           id="isbn" 
                                           name="isbn" 
                                           value="{{ old('isbn', $buku->isbn) }}" 
                                           required>
                                    @error('isbn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">ISBN unik standar internasional.</div>
                            </div>

                            <!-- Kategori -->
                            <div class="col-md-6">
                                <label for="kategori_id" class="form-label fw-semibold">Kategori Buku <span class="text-danger">*</span></label>
                                <select class="form-select @error('kategori_id') is-invalid @enderror" 
                                        id="kategori_id" 
                                        name="kategori_id" 
                                        required>
                                    <option value="" disabled>-- Pilih Kategori --</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_id', $buku->kategori_id) == $kategori->id ? 'selected' : '' }}>
                                            [{{ $kategori->kode_kategori }}] {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Judul Buku -->
                            <div class="col-12">
                                <label for="judul" class="form-label fw-semibold">Judul Buku <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('judul') is-invalid @enderror" 
                                       id="judul" 
                                       name="judul" 
                                       value="{{ old('judul', $buku->judul) }}" 
                                       required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Penulis -->
                            <div class="col-md-6">
                                <label for="penulis" class="form-label fw-semibold">Nama Penulis / Pengarang <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" 
                                           class="form-control @error('penulis') is-invalid @enderror" 
                                           id="penulis" 
                                           name="penulis" 
                                           value="{{ old('penulis', $buku->penulis) }}" 
                                           required>
                                    @error('penulis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Penerbit -->
                            <div class="col-md-6">
                                <label for="penerbit" class="form-label fw-semibold">Nama Penerbit <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                    <input type="text" 
                                           class="form-control @error('penerbit') is-invalid @enderror" 
                                           id="penerbit" 
                                           name="penerbit" 
                                           value="{{ old('penerbit', $buku->penerbit) }}" 
                                           required>
                                    @error('penerbit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tahun Terbit -->
                            <div class="col-md-6">
                                <label for="tahun_terbit" class="form-label fw-semibold">Tahun Terbit <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                    <input type="number" 
                                           class="form-control @error('tahun_terbit') is-invalid @enderror" 
                                           id="tahun_terbit" 
                                           name="tahun_terbit" 
                                           min="1900" 
                                           max="{{ date('Y') + 1 }}" 
                                           value="{{ old('tahun_terbit', $buku->tahun_terbit) }}" 
                                           required>
                                    @error('tahun_terbit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Stok -->
                            <div class="col-md-6">
                                <label for="stok" class="form-label fw-semibold">Jumlah Stok Fisik <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-box-seam"></i></span>
                                    <input type="number" 
                                           class="form-control @error('stok') is-invalid @enderror" 
                                           id="stok" 
                                           name="stok" 
                                           min="0" 
                                           value="{{ old('stok', $buku->stok) }}" 
                                           required>
                                    @error('stok')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Sinopsis -->
                            <div class="col-12">
                                <label for="sinopsis" class="form-label fw-semibold">Sinopsis / Ringkasan Deskripsi</label>
                                <textarea class="form-control @error('sinopsis') is-invalid @enderror" 
                                          id="sinopsis" 
                                          name="sinopsis" 
                                          rows="4">{{ old('sinopsis', $buku->sinopsis) }}</textarea>
                                @error('sinopsis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('buku.index') }}" class="btn btn-light border px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-del px-4">
                                <i class="bi bi-check2-circle me-1"></i> Perbarui Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
