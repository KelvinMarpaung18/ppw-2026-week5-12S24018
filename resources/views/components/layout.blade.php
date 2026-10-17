<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'SIPUS-Del' }} — Perpustakaan Institut Teknologi Del</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Custom Theme Styling: Del Royal Purple -->
    <style>
        :root {
            --del-purple-dark: #3B0764;
            --del-purple: #4C1D95;
            --del-purple-light: #6D28D9;
            --del-purple-subtle: #F3E8FF;
            --del-accent: #8B5CF6;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-del {
            background: linear-gradient(135deg, var(--del-purple) 0%, var(--del-purple-dark) 100%);
            box-shadow: 0 4px 12px rgba(76, 29, 149, 0.15);
        }

        .navbar-brand-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .nav-link {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85) !important;
            transition: all 0.2s ease;
            border-radius: 6px;
            padding: 0.5rem 0.85rem !important;
        }

        .nav-link:hover, .nav-link.active {
            color: #FFFFFF !important;
            background: rgba(255, 255, 255, 0.15);
        }

        .btn-del {
            background-color: var(--del-purple);
            color: #FFFFFF;
            border: 1px solid var(--del-purple);
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-del:hover, .btn-del:focus {
            background-color: var(--del-purple-dark);
            color: #FFFFFF;
            border-color: var(--del-purple-dark);
            box-shadow: 0 4px 10px rgba(76, 29, 149, 0.25);
        }

        .btn-outline-del {
            color: var(--del-purple);
            border-color: var(--del-purple);
            font-weight: 500;
        }

        .btn-outline-del:hover {
            background-color: var(--del-purple);
            color: #FFFFFF;
            border-color: var(--del-purple);
        }

        .badge-del {
            background-color: var(--del-purple-subtle);
            color: var(--del-purple-dark);
            font-weight: 600;
            border: 1px solid rgba(76, 29, 149, 0.15);
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            background: #FFFFFF;
        }

        .card-header-custom {
            background-color: transparent;
            border-bottom: 1px solid #F1F5F9;
            padding: 1.25rem 1.5rem;
        }

        .table > :not(caption) > * > * {
            padding: 0.95rem 1rem;
            vertical-align: middle;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--del-purple);
            border-color: var(--del-purple);
        }

        .pagination .page-link {
            color: var(--del-purple);
        }

        .footer-del {
            margin-top: auto;
            background: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            padding: 1.5rem 0;
            font-size: 0.9rem;
            color: #64748B;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('buku.index') }}">
                <i class="bi bi-book-half fs-4"></i>
                <span>SIPUS-Del</span>
                <span class="navbar-brand-badge">Institut Teknologi Del</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('buku.*') ? 'active' : '' }}" href="{{ route('buku.index') }}">
                            <i class="bi bi-journal-bookmark me-1"></i> Koleksi Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}" href="{{ route('peminjaman.index') }}">
                            <i class="bi bi-arrow-left-right me-1"></i> Sirkulasi Peminjaman
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kategori.*') ? 'active' : '' }}" href="{{ route('kategori.index') }}">
                            <i class="bi bi-tags me-1"></i> Kategori Buku
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    @auth
                        <a href="{{ route('peminjaman.create') }}" class="btn btn-sm btn-warning text-dark fw-semibold px-3 py-2 shadow-sm">
                            <i class="bi bi-arrow-left-right me-1"></i> Catat Pinjam
                        </a>
                        <a href="{{ route('buku.create') }}" class="btn btn-sm btn-light fw-semibold px-3 py-2 shadow-sm" style="color: var(--del-purple) !important;">
                            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Buku
                        </a>

                        <div class="dropdown ms-2">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle px-3 py-2 d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-6"></i>
                                <span>{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li class="dropdown-header">
                                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                                    <div class="small text-muted">{{ Auth::user()->email }}</div>
                                    @if(session('login_timestamp'))
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-clock me-1"></i>Login: {{ session('login_timestamp') }}
                                        </div>
                                    @endif
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                            <i class="bi bi-box-arrow-right"></i> Keluar (Logout)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-sm btn-light fw-semibold px-3 py-2 shadow-sm" style="color: var(--del-purple) !important;">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login Pustakawan
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container py-4 flex-grow-1">
        <!-- Flash Session Notifications -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show card-custom border-0 shadow-sm mb-4 d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                <div class="flex-grow-1">
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show card-custom border-0 shadow-sm mb-4 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                <div class="flex-grow-1">
                    <strong>Peringatan!</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show card-custom border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-x-circle-fill fs-4 me-2 text-danger"></i>
                    <strong>Terdapat kesalahan pengisian data pada formulir:</strong>
                </div>
                <ul class="mb-0 ps-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="footer-del">
        <div class="container text-center">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start mb-2 mb-md-0">
                    <span class="fw-semibold text-dark">SIPUS-Del</span> — Sistem Informasi Perpustakaan Institut Teknologi Del
                </div>
                <div class="col-md-6 text-md-end">
                    <small>Praktikum Pemrograman & Pengujian Web &copy; {{ date('Y') }}</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
