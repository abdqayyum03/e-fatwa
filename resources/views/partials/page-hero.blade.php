{{--
    Page header for inner pages.
    @include('partials.page-hero', [
        'title' => 'Senarai Fatwa', 'subtitle' => '...', 'icon' => 'journal-text',
        'crumbs' => ['Fatwa' => route('fatwas.index')],   // optional, last item is the current page
    ])
    Anything in @section('hero-actions') is shown on the right.
--}}
<header class="page-hero">
    <div class="container-xl">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Utama</a></li>
                @foreach(($crumbs ?? []) as $label => $url)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($label, 60) }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            @isset($icon)
                <div class="hero-icon"><i class="bi bi-{{ $icon }}"></i></div>
            @endisset
            <div class="flex-grow-1" style="min-width: 0">
                <h1>{{ $title }}</h1>
                @isset($subtitle)
                    <p>{{ $subtitle }}</p>
                @endisset
            </div>
            @hasSection('hero-actions')
                <div class="d-flex gap-2 flex-wrap">@yield('hero-actions')</div>
            @endif
        </div>
    </div>
</header>
