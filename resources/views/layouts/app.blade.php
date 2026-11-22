<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ __('e-Fatwa: pusat rujukan fatwa yang diwartakan oleh jabatan mufti negeri di seluruh Malaysia.') }}">
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
    <a href="#main" class="skip-link btn btn-primary btn-sm">{{ __('Langkau ke kandungan') }}</a>
    <div class="reading-progress" aria-hidden="true"></div>

    <nav class="navbar navbar-expand-xl ef-nav py-2" aria-label="{{ __('Navigasi utama') }}">
        <div class="container-xl">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <span class="brand-mark"><i class="bi bi-journal-bookmark-fill"></i></span>
                e-Fatwa
            </a>

            <div class="d-flex align-items-center gap-2 order-xl-last">
                {{-- Language switch --}}
                <a class="icon-btn text-decoration-none fw-bold small" style="width: auto; padding: 0 .7rem; border-radius: 999px"
                   href="{{ route('locale', app()->getLocale() === 'ms' ? 'en' : 'ms') }}"
                   title="{{ app()->getLocale() === 'ms' ? 'Switch to English' : 'Tukar ke Bahasa Melayu' }}" lang="{{ app()->getLocale() === 'ms' ? 'en' : 'ms' }}">
                    {{ app()->getLocale() === 'ms' ? 'EN' : 'BM' }}
                </a>
                <button type="button" class="icon-btn" id="themeToggle" aria-label="{{ __('Tukar tema') }}"><i class="bi bi-moon-stars"></i></button>
                @auth
                    <div class="dropdown">
                        <button class="icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Akaun') }}">
                            <i class="bi bi-person-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                            <li><a class="dropdown-item" href="{{ route('account.edit') }}"><i class="bi bi-sliders me-2"></i>{{ __('Akaun saya') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('bookmarks.index') }}"><i class="bi bi-bookmark-heart me-2"></i>{{ __('Simpanan saya') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('ask.index') }}"><i class="bi bi-stars me-2"></i>{{ __('Tanya AI') }}</a></li>
                            @can('admin')
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">{{ __('Admin') }}</h6></li>
                                <li><a class="dropdown-item" href="{{ route('fatwas.create') }}"><i class="bi bi-plus-circle me-2"></i>{{ __('Tambah Fatwa') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('preachers.create') }}"><i class="bi bi-person-plus me-2"></i>{{ __('Tambah Pendakwah') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('mosques.create') }}"><i class="bi bi-building-add me-2"></i>{{ __('Tambah Masjid') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('lectures.create') }}"><i class="bi bi-calendar-plus me-2"></i>{{ __('Tambah Kuliah') }}</a></li>
                            @endcan
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>{{ __('Log Keluar') }}</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="icon-btn" href="{{ route('login') }}" aria-label="{{ __('Log masuk') }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Log masuk') }}">
                        <i class="bi bi-person"></i>
                    </a>
                @endauth
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                        aria-controls="mainNav" aria-expanded="false" aria-label="{{ __('Buka menu') }}">
                    <i class="bi bi-list fs-2"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-xl-3 my-2 my-xl-0">
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('fatwas.index', 'fatwas.show')) active @endif" href="{{ route('fatwas.index') }}"><i class="bi bi-journal-text"></i>{{ __('Fatwa') }}</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('fatwas.compare')) active @endif" href="{{ route('fatwas.compare') }}"><i class="bi bi-columns-gap"></i>{{ __('Bandingkan') }}</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('ask.*')) active @endif" href="{{ route('ask.index') }}"><i class="bi bi-stars"></i>{{ __('Tanya AI') }}</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('authorities.*')) active @endif" href="{{ route('authorities.index') }}"><i class="bi bi-bank"></i>{{ __('Agensi') }}</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('preachers.*')) active @endif" href="{{ route('preachers.index') }}"><i class="bi bi-people"></i>{{ __('Pendakwah') }}</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('mosques.*')) active @endif" href="{{ route('mosques.map') }}"><i class="bi bi-geo-alt"></i>{{ __('Masjid') }}</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('lectures.*')) active @endif" href="{{ route('lectures.index') }}"><i class="bi bi-calendar-event"></i>{{ __('Kuliah') }}</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('statistics')) active @endif" href="{{ route('statistics') }}"><i class="bi bi-bar-chart"></i>{{ __('Statistik') }}</a></li>
                </ul>

                @unless(request()->routeIs('dashboard'))
                    <form class="nav-search ms-xl-auto mb-3 mb-xl-0" role="search" action="{{ route('fatwas.index') }}" method="GET">
                        <i class="bi bi-search"></i>
                        <input class="form-control form-control-sm" type="search" name="search" placeholder="{{ __('Cari fatwa...') }}"
                               aria-label="{{ __('Cari fatwa') }}" data-suggest="{{ route('fatwas.suggest') }}" value="{{ request()->routeIs('fatwas.index') ? request('search') : '' }}">
                        <kbd class="d-none d-xxl-inline">/</kbd>
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
                    <p class="mb-0">{{ __('Pusat rujukan fatwa yang diwartakan oleh jabatan mufti di seluruh Malaysia, dikumpulkan di satu tempat.') }}</p>
                    <p class="small mt-3 mb-0 opacity-75">
                        {{ __('Kandungan disalin daripada laman rasmi jabatan mufti. Sentiasa rujuk dokumen warta rasmi untuk keputusan yang sah.') }}
                    </p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-1">
                    <h6>{{ __('Fatwa') }}</h6>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><a href="{{ route('fatwas.index') }}">{{ __('Semua fatwa') }}</a></li>
                        <li><a href="{{ route('fatwas.compare') }}">{{ __('Bandingkan negeri') }}</a></li>
                        <li><a href="{{ route('ask.index') }}">{{ __('Tanya AI') }}</a></li>
                        <li><a href="{{ route('authorities.index') }}">{{ __('Agensi') }}</a></li>
                        <li><a href="{{ route('statistics') }}">{{ __('Statistik') }}</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6>{{ __('Direktori') }}</h6>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><a href="{{ route('preachers.index') }}">{{ __('Pendakwah') }}</a></li>
                        <li><a href="{{ route('mosques.map') }}">{{ __('Peta masjid') }}</a></li>
                        <li><a href="{{ route('mosques.index') }}">{{ __('Senarai masjid') }}</a></li>
                        <li><a href="{{ route('lectures.index') }}">{{ __('Jadual kuliah') }}</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6>{{ __('Akaun') }}</h6>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        @auth
                            <li><a href="{{ route('account.edit') }}">{{ __('Akaun saya') }}</a></li>
                            <li><a href="{{ route('bookmarks.index') }}">{{ __('Simpanan saya') }}</a></li>
                        @else
                            <li><a href="{{ route('login') }}">{{ __('Log masuk') }}</a></li>
                            <li><a href="{{ route('register') }}">{{ __('Daftar') }}</a></li>
                        @endauth
                        <li><a href="{{ route('feedbacks.index') }}">{{ __('Maklum balas') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="fine d-flex flex-wrap justify-content-between gap-2">
                <span>© {{ date('Y') }} e-Fatwa · {{ __('Projek Tahun Akhir, UNITEN') }}</span>
                <span class="opacity-75">{!! __('Tekan :key untuk mencari', ['key' => '<kbd>/</kbd>']) !!}</span>
            </div>
        </div>
    </footer>

    <button type="button" class="to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="bi bi-arrow-up"></i></button>

    @if(session('success') || session('error'))
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div class="toast align-items-center" role="status" aria-live="polite">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi {{ session('error') ? 'bi-exclamation-circle text-danger' : 'bi-check-circle-fill text-success' }} me-2"></i>
                        {{ session('success') ?? session('error') }}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Tutup') }}"></button>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>window.efI18n = {{ \Illuminate\Support\Js::from(collect(['Tukar ke mod cerah', 'Tukar ke mod gelap', 'Lihat semua hasil untuk “:q”', 'Tiada cadangan. Cari “:q” dalam teks penuh →', 'Memuatkan…', 'Ralat', 'Waktu solat tidak dapat dimuatkan.', 'Sedang berfikir…', 'Disalin', 'Gagal'])->mapWithKeys(fn ($k) => [$k => __($k)])) }};</script>
    @stack('scripts')
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
