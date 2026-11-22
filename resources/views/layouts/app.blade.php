<!DOCTYPE html>
<html lang="ms" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="e-Fatwa: pusat rujukan fatwa yang diwartakan oleh jabatan mufti negeri di seluruh Malaysia.">
    <title>@hasSection('title')@yield('title') · @endif e-Fatwa Malaysia</title>

    {{-- Apply the saved theme before first paint to avoid a flash. --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('ef-theme');
                if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}">
    @stack('head')
</head>
<body>
    <a href="#main" class="skip-link btn btn-primary btn-sm">Langkau ke kandungan</a>
    <div class="reading-progress" aria-hidden="true"></div>

    <nav class="navbar navbar-expand-xl ef-nav py-2" aria-label="Navigasi utama">
        <div class="container-xl">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <span class="brand-mark"><i class="bi bi-journal-bookmark-fill"></i></span>
                e-Fatwa
            </a>

            <div class="d-flex align-items-center gap-2 order-xl-last">
                <button type="button" class="icon-btn" id="themeToggle" aria-label="Tukar tema"><i class="bi bi-moon-stars"></i></button>
                @auth
                    <div class="dropdown">
                        <button class="icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Akaun">
                            <i class="bi bi-person-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                            @can('admin')
                                <li><a class="dropdown-item" href="{{ route('fatwas.create') }}"><i class="bi bi-plus-circle me-2"></i>Tambah Fatwa</a></li>
                                <li><a class="dropdown-item" href="{{ route('preachers.create') }}"><i class="bi bi-person-plus me-2"></i>Tambah Pendakwah</a></li>
                                <li><a class="dropdown-item" href="{{ route('mosques.create') }}"><i class="bi bi-building-add me-2"></i>Tambah Masjid</a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endcan
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Log Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="icon-btn" href="{{ route('login') }}" aria-label="Log masuk admin" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Log masuk admin">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                @endauth
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                        aria-controls="mainNav" aria-expanded="false" aria-label="Buka menu">
                    <i class="bi bi-list fs-2"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-xl-3 my-2 my-xl-0">
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('fatwas.index', 'fatwas.show')) active @endif" href="{{ route('fatwas.index') }}"><i class="bi bi-journal-text"></i>Fatwa</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('fatwas.compare')) active @endif" href="{{ route('fatwas.compare') }}"><i class="bi bi-columns-gap"></i>Bandingkan</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('authorities.*')) active @endif" href="{{ route('authorities.index') }}"><i class="bi bi-bank"></i>Agensi</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('preachers.*')) active @endif" href="{{ route('preachers.index') }}"><i class="bi bi-people"></i>Pendakwah</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('mosques.*')) active @endif" href="{{ route('mosques.map') }}"><i class="bi bi-geo-alt"></i>Masjid</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('feedbacks.*')) active @endif" href="{{ route('feedbacks.index') }}"><i class="bi bi-chat-heart"></i>Maklum Balas</a></li>
                </ul>

                @unless(request()->routeIs('dashboard'))
                    <form class="nav-search ms-xl-auto mb-3 mb-xl-0" role="search" action="{{ route('fatwas.index') }}" method="GET">
                        <i class="bi bi-search"></i>
                        <input class="form-control form-control-sm" type="search" name="search" placeholder="Cari fatwa..."
                               aria-label="Cari fatwa" data-suggest="{{ route('fatwas.suggest') }}" value="{{ request()->routeIs('fatwas.index') ? request('search') : '' }}">
                        <kbd class="d-none d-xl-inline">/</kbd>
                    </form>
                @endunless
            </div>
        </div>
    </nav>

    <main id="main">
        @yield('content')
    </main>

    <footer class="ef-footer">
        <div class="container-xl">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="brand mb-2"><i class="bi bi-journal-bookmark-fill text-gold"></i> e-Fatwa</div>
                    <p class="mb-0">Pusat rujukan fatwa yang diwartakan oleh jabatan mufti di seluruh Malaysia, dikumpulkan di satu tempat.</p>
                    <p class="small mt-3 mb-0 opacity-75">
                        Kandungan disalin daripada laman rasmi jabatan mufti. Sentiasa rujuk dokumen warta rasmi untuk keputusan yang sah.
                    </p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-1">
                    <h6>Fatwa</h6>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><a href="{{ route('fatwas.index') }}">Semua fatwa</a></li>
                        <li><a href="{{ route('fatwas.compare') }}">Bandingkan negeri</a></li>
                        <li><a href="{{ route('authorities.index') }}">Agensi</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6>Direktori</h6>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><a href="{{ route('preachers.index') }}">Pendakwah</a></li>
                        <li><a href="{{ route('mosques.map') }}">Peta masjid</a></li>
                        <li><a href="{{ route('mosques.index') }}">Senarai masjid</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6>Sistem</h6>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><a href="{{ route('feedbacks.index') }}">Maklum balas</a></li>
                        <li><a href="{{ route('login') }}">Log masuk admin</a></li>
                    </ul>
                </div>
            </div>
            <div class="fine d-flex flex-wrap justify-content-between gap-2">
                <span>© {{ date('Y') }} e-Fatwa · Projek Tahun Akhir, UNITEN</span>
                <span class="opacity-75">Tekan <kbd>/</kbd> untuk mencari</span>
            </div>
        </div>
    </footer>

    <button type="button" class="to-top" aria-label="Kembali ke atas"><i class="bi bi-arrow-up"></i></button>

    @if(session('success') || session('error'))
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div class="toast align-items-center" role="status" aria-live="polite">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi {{ session('error') ? 'bi-exclamation-circle text-danger' : 'bi-check-circle-fill text-success' }} me-2"></i>
                        {{ session('success') ?? session('error') }}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
