@extends('layouts.app')

@section('title', __('Pusat Rujukan Fatwa Malaysia'))

@section('content')
{{-- ========== Hero ========== --}}
<section class="home-hero">
    <div class="container-xl position-relative">
        <div class="row align-items-center gx-4 gy-5">
            <div class="col-lg-7">
                <div class="arabic mb-3" lang="ar" aria-hidden="true">فتوى</div>
                <h1>{{ __('Pusat Rujukan Fatwa Malaysia') }}</h1>
                <p class="lead mt-3 mb-4">
                    {{ __('Cari, baca dan bandingkan fatwa daripada :count jabatan mufti negeri, terus daripada laman rasmi mereka.', ['count' => $stats['states']]) }}
                </p>

                <form class="hero-search" role="search" action="{{ route('fatwas.index') }}" method="GET">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search" class="form-control" placeholder="{{ __('Cari isu, cth. zakat, wakaf, ajaran sesat...') }}"
                           aria-label="{{ __('Cari fatwa') }}" data-suggest="{{ route('fatwas.suggest') }}" data-primary-search>
                    <button type="submit" class="btn btn-gold">{{ __('Cari') }}</button>
                </form>

                <div class="hero-topics mt-3">
                    <span>{{ __('Carian popular') }}</span>
                    @foreach(['zakat', 'ahli sunnah', 'wakaf', 'ajaran', 'faraid', 'vaksin'] as $topic)
                        <a class="chip" href="{{ route('fatwas.index', ['search' => $topic]) }}">{{ $topic }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Latest gazetted fatwas, set like gazette entries --}}
            <div class="col-lg-5 d-none d-lg-block">
                <div class="gazette-card">
                    <div class="gazette-card-head">
                        <span>{{ __('Fatwa terkini') }}</span>
                        <span>{{ __('daripada :count negeri', ['count' => $stats['states']]) }}</span>
                    </div>
                    @foreach($latest->take(3) as $fatwa)
                        <a href="{{ route('fatwas.show', $fatwa) }}" class="gazette-entry">
                            <span class="gazette-entry-title">{{ \Illuminate\Support\Str::limit($fatwa->title, 90) }}</span>
                            <span class="gazette-entry-meta">
                                <x-state-pill :state="$fatwa->authority->state ?? null" />
                                <span class="gazette-ref">{{ collect([$fatwa->gazette_no, $fatwa->date_issued ?? $fatwa->issued_year])->filter()->join(' · ') }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== Stats ========== --}}
<div class="container-xl stats-strip">
    <div class="surface">
        <div class="row g-0">
            <div class="col-6 col-md-3 stat"><div class="num">{{ number_format($stats['fatwas']) }}</div><div class="lbl">{{ __('Fatwa dalam pangkalan data') }}</div></div>
            <div class="col-6 col-md-3 stat"><div class="num">{{ number_format($stats['with_text']) }}</div><div class="lbl">{{ __('Boleh dicari teks penuh') }}</div></div>
            <div class="col-6 col-md-3 stat"><div class="num">{{ $stats['states'] }}</div><div class="lbl">{{ __('Negeri diliputi') }}</div></div>
            <div class="col-6 col-md-3 stat"><div class="num">{{ $stats['authorities'] }}</div><div class="lbl">{{ __('Agensi berautoriti') }}</div></div>
        </div>
    </div>
</div>

{{-- ========== Features ========== --}}
<section class="section">
    <div class="container-xl">
        <div class="section-title">
            <div>
                <div class="eyebrow">{{ __('Teroka') }}</div>
                <h2>{{ __('Mula di sini') }}</h2>
            </div>
        </div>
        @php
            $features = [
                ['route' => 'fatwas.index', 'icon' => 'journal-text', 'title' => __('Cari Fatwa'), 'desc' => __('Carian teks penuh merentas tajuk dan kandungan fatwa, ditapis mengikut negeri, tahun dan kategori.'), 'go' => __('Mula mencari')],
                ['route' => 'fatwas.compare', 'icon' => 'columns-gap', 'title' => __('Bandingkan Negeri'), 'desc' => __('Lihat bagaimana setiap negeri memutuskan isu yang sama, disusun bersebelahan.'), 'go' => __('Bandingkan')],
                ['route' => 'mosques.map', 'icon' => 'geo-alt', 'title' => __('Peta Masjid'), 'desc' => __('Cari lokasi masjid pada peta interaktif dan dapatkan arah ke sana.'), 'go' => __('Buka peta')],
                ['route' => 'preachers.index', 'icon' => 'people', 'title' => __('Pendakwah Bertauliah'), 'desc' => __('Direktori pendakwah yang ditauliahkan oleh agensi agama negeri.'), 'go' => __('Lihat direktori')],
            ];
        @endphp
        <div class="row g-3">
            @foreach($features as $f)
                <div class="col-sm-6 col-lg-3">
                    <a href="{{ route($f['route']) }}" class="surface lift feature">
                        <i class="bi bi-{{ $f['icon'] }} feature-icon"></i>
                        <h3>{{ $f['title'] }}</h3>
                        <p>{{ $f['desc'] }}</p>
                        <span class="go">{{ $f['go'] }} <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========== Coverage + categories ========== --}}
<section class="section pattern-bg">
    <div class="container-xl">
        <div class="row gx-4 gy-5">
            <div class="col-lg-7">
                <div class="section-title">
                    <div>
                        <div class="eyebrow">{{ __('Liputan') }}</div>
                        <h2>{{ __('Fatwa mengikut negeri') }}</h2>
                    </div>
                    <a href="{{ route('fatwas.compare') }}" class="btn btn-outline-primary btn-sm">{{ __('Bandingkan') }} <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="surface p-4">
                    @php($max = max(1, $perState->max() ?? 1))
                    @forelse($perState as $state => $count)
                        <a class="bar-row" href="{{ route('fatwas.index', ['state' => $state]) }}">
                            <span class="bar-label">{{ $state }}</span>
                            <div class="bar-track"><div class="bar-fill" style="width: {{ max(1, round($count / $max * 100)) }}%"></div></div>
                            <span class="count">{{ number_format($count) }}</span>
                        </a>
                    @empty
                        <div class="empty py-4"><i class="bi bi-database"></i>{{ __('Belum ada fatwa. Jalankan') }} <code>{{ __('php artisan fatwa:scrape') }}</code>.</div>
                    @endforelse
                    <p class="small text-muted mb-0 mt-3">
                        {{ __('Sumber: laman rasmi jabatan mufti :states.', ['states' => $perState->keys()->sort()->join(', ')]) }}
                    </p>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="section-title">
                    <div>
                        <div class="eyebrow">{{ __('Hari ini') }}</div>
                        <h2>{{ __('Waktu solat') }}</h2>
                    </div>
                </div>
                <div class="mb-5">@include('partials.prayer-times')</div>

                <div class="section-title">
                    <div>
                        <div class="eyebrow">{{ __('Topik') }}</div>
                        <h2>{{ __('Kategori') }}</h2>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($categories as $category)
                        <a href="{{ route('fatwas.index', ['category' => $category->id]) }}" class="chip chip-light">
                            {{ $category->category_name }} <span class="chip-count">{{ number_format($category->fatwas_count) }}</span>
                        </a>
                    @empty
                        <span class="text-muted">{{ __('Tiada kategori lagi.') }}</span>
                    @endforelse
                </div>

                <div class="surface p-4 mt-4">
                    <h3 class="h5">{{ __('Apakah itu fatwa?') }}</h3>
                    <p class="text-muted small mb-0">
                        {!! __('Fatwa ialah keputusan hukum rasmi oleh pihak berautoriti Islam sebagai panduan masyarakat dalam hal akidah, ibadah, muamalat dan kehidupan seharian. Di Malaysia, setiap negeri mempunyai Jawatankuasa Fatwa sendiri, dan fatwa yang :gazetted mengikat umat Islam di negeri tersebut.', ['gazetted' => '<strong>'.e(__('diwartakan')).'</strong>']) !!}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== Latest ========== --}}
<section class="section">
    <div class="container-xl">
        <div class="section-title">
            <div>
                <div class="eyebrow">{{ __('Terkini') }}</div>
                <h2>{{ __('Fatwa terbaharu') }}</h2>
            </div>
            <a href="{{ route('fatwas.index') }}" class="btn btn-outline-primary btn-sm">{{ __('Semua fatwa') }} <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-3">
            @forelse($latest as $fatwa)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('fatwas.show', $fatwa) }}" class="surface lift latest-card">
                        <div class="meta-row mt-0">
                            <x-state-pill :state="$fatwa->authority->state ?? null" />
                            @if($fatwa->status === 'Diwartakan')<span class="tag tag-gold">{{ __('Diwartakan') }}</span>@endif
                        </div>
                        <h3 class="latest-card-title">{{ \Illuminate\Support\Str::limit($fatwa->title, 120) }}</h3>
                        <div class="latest-card-foot">
                            <span class="gazette-ref">{{ collect([$fatwa->gazette_no, $fatwa->date_issued ?? $fatwa->issued_year])->filter()->join(' · ') ?: __('Tiada tarikh') }}</span>
                            <span class="go">{{ __('Baca') }} <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="empty"><i class="bi bi-journal"></i>{{ __('Belum ada fatwa.') }}</div></div>
            @endforelse
        </div>

        <div class="surface feedback-cta mt-5">
            <div>
                <h2 class="h4 mb-1">{{ __('Ada cadangan untuk e-Fatwa?') }}</h2>
                <p class="text-muted mb-0">{{ __('Kongsi pandangan anda atau laporkan masalah. Setiap maklum balas membantu kami menambah baik sistem ini.') }}</p>
            </div>
            <a href="{{ route('feedbacks.index') }}" class="btn btn-primary">{{ __('Hantar Maklum Balas') }}</a>
        </div>
    </div>
</section>
@endsection
