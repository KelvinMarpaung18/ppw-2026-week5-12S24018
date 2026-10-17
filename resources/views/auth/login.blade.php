<x-layout>
    <x-slot:title>Login Pustakawan</x-slot:title>

    <div class="row justify-content-center py-4">
        <div class="col-md-6 col-lg-5">
            <div class="card card-custom shadow-sm border-0">
                <div class="card-header card-header-custom text-center py-4" style="background: linear-gradient(135deg, var(--del-purple) 0%, var(--del-purple-dark) 100%); color: #ffffff; border-radius: 12px 12px 0 0;">
                    <div class="mb-2">
                        <i class="bi bi-shield-lock-fill fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Autentikasi Pustakawan</h4>
                    <p class="small text-white-50 mb-0">Sistem Informasi Perpustakaan Kampus Del</p>
                </div>

                <div class="card-body p-4">

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf

                        <!-- Input Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email Pustakawan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', request()->cookie('del_last_user_email')) }}" 
                                       placeholder="nama@del.ac.id" 
                                       required 
                                       autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if(request()->cookie('del_last_user_email'))
                                <div class="form-text text-success small">
                                    <i class="bi bi-cookie me-1"></i>Email terisi otomatis dari Cookie sesi Anda (HttpOnly).
                                </div>
                            @endif
                        </div>

                        <!-- Input Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="••••••••" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Ingat Saya (Remember Me) -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="remember">
                                    Ingat sesi saya
                                </label>
                            </div>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-del py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem Pustakawan
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-light text-center py-3 border-0 small text-muted">
                    Institut Teknologi Del &bull; Sistem Informasi Perpustakaan
                </div>
            </div>
        </div>
    </div>
</x-layout>
