<x-layout>
    <x-slot:title>Edit Kategori — {{ $kategori->nama_kategori }}</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}" style="color: var(--del-purple);">Kategori Buku</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Kategori</li>
                    </ol>
                </nav>
                <a href="{{ route('kategori.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card card-custom">
                <div class="card-header card-header-custom">
                    <h5 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-pencil-square me-2" style="color: var(--del-purple);"></i>
                        Formulir Perubahan Kategori
                    </h5>
                    <p class="text-muted small mb-0">Perbarui informasi kode atau nama klasifikasi kategori buku.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('kategori.update', $kategori) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Kode Kategori -->
                        <div class="mb-3">
                            <label for="kode_kategori" class="form-label fw-semibold">Kode Kategori <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-upc"></i></span>
                                <input type="text" 
                                       class="form-control @error('kode_kategori') is-invalid @enderror" 
                                       id="kode_kategori" 
                                       name="kode_kategori" 
                                       value="{{ old('kode_kategori', $kategori->kode_kategori) }}" 
                                       required>
                                @error('kode_kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Nama Kategori -->
                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_kategori') is-invalid @enderror" 
                                   id="nama_kategori" 
                                   name="nama_kategori" 
                                   value="{{ old('nama_kategori', $kategori->nama_kategori) }}" 
                                   required>
                            @error('nama_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('kategori.index') }}" class="btn btn-light border px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-del px-4">
                                <i class="bi bi-check2-circle me-1"></i> Perbarui Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
