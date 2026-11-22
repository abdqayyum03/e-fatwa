@extends('layouts.app')

@section('title', 'Pusat Rujukan Fatwa Malaysia')

@section('content')
{{-- ========== Hero ========== --}}
<section class="home-hero">
    <div class="hero-clip" aria-hidden="true">
        <span class="hero-orb o1"></span>
        <span class="hero-orb o2"></span>
    </div>
    <div class="container-xl position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-8">
                <div class="arabic mb-3 reveal" lang="ar" aria-hidden="true">فتوى</div>
                <h1 class="reveal" style="--d:.05s">Pusat Rujukan Fatwa Malaysia</h1>
                <p class="lead my-4 reveal" style="--d:.1s">
                    Cari, baca dan bandingkan fatwa yang diwartakan oleh jabatan mufti di seluruh negara.
                    Semua di satu tempat, terus daripada sumber rasmi.
                </p>

                <form class="hero-search reveal" style="--d:.15s" role="search" action="{{ route('fatwas.index') }}" method="GET">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search" class="form-control" placeholder="Cari isu, cth. zakat, wakaf, ajaran sesat..."
                           aria-label="Cari fatwa" data-suggest="{{ route('fatwas.suggest') }}" data-primary-search>
                    <button type="submit" class="btn btn-gold">Cari <i class="bi bi-arrow-right"></i></button>
                </form>

                <div class="d-flex flex-wrap gap-2 mt-4 reveal" style="--d:.2s">
                    <span class="small text-white-50 me-1 align-self-center">Popular:</span>
                    @foreach(['zakat', 'ahli sunnah', 'wakaf', 'ajaran', 'faraid', 'vaksin'] as $topic)
                        <a class="chip" href="{{ route('fatwas.index', ['search' => $topic]) }}">{{ $topic }}</a>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block">
                <div class="surface p-4 reveal text-body" style="--d:.25s">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge rounded-pill text-bg-success"><i class="bi bi-broadcast"></i> Terkini</span>
                        <small class="text-muted">daripada {{ $stats['states'] }} negeri</small>
                    </div>
                    @foreach($latest->take(3) as $fatwa)
                        <a href="{{ route('fatwas.show', $fatwa) }}" class="d-block text-decoration-none text-body py-2 @if(! $loop->last) border-bottom @endif">
                            <div class="fw-semibold small lh-sm">{{ \Illuminate\Support\Str::limit($fatwa->title, 90) }}</div>
                            <div class="meta-row mt-1">
                                <x-state-pill :state="$fatwa->authority->state ?? null" />
                                <span class="tag">{{ $fatwa->issued_year }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== Stats ========== --}}
<div class="container-xl stats-strip">
    <div class="surface reveal">
        <div class="row g-0">
            <div class="col-6 col-md-3 stat"><div class="num" data-count="{{ $stats['fatwas'] }}">0</div><div class="lbl">Fatwa dalam pangkalan data</div></div>
            <div class="col-6 col-md-3 stat"><div class="num" data-count="{{ $stats['with_text'] }}">0</div><div class="lbl">Boleh dicari teks penuh</div></div>
            <div class="col-6 col-md-3 stat"><div class="num" data-count="{{ $stats['states'] }}">0</div><div class="lbl">Negeri diliputi</div></div>
            <div class="col-6 col-md-3 stat"><div class="num" data-count="{{ $stats['authorities'] }}">0</div><div class="lbl">Agensi berautoriti</div></div>
        </div>
    </div>
</div>

{{-- ========== Features ========== --}}
<section class="section">
    <div class="container-xl">
        <div class="section-title reveal">
            <div>
                <div class="eyebrow">Teroka</div>
                <h2>Apa yang boleh anda lakukan</h2>
            </div>
        </div>
        @php
            $features = [
                ['route' => 'fatwas.index', 'icon' => 'journal-text', 'tone' => 'emerald', 'title' => 'Cari Fatwa', 'desc' => 'Carian teks penuh merentas tajuk dan kandungan fatwa, ditapis mengikut negeri, tahun dan kategori.', 'go' => 'Mula mencari'],
                ['route' => 'fatwas.compare', 'icon' => 'columns-gap', 'tone' => 'gold', 'title' => 'Bandingkan Negeri', 'desc' => 'Lihat bagaimana setiap negeri memutuskan isu yang sama, disusun bersebelahan.', 'go' => 'Bandingkan'],
                ['route' => 'mosques.map', 'icon' => 'geo-alt', 'tone' => 'sky', 'title' => 'Peta Masjid', 'desc' => 'Cari lokasi masjid pada peta interaktif dan dapatkan arah ke sana.', 'go' => 'Buka peta'],
                ['route' => 'preachers.index', 'icon' => 'people', 'tone' => 'rose', 'title' => 'Pendakwah Bertauliah', 'desc' => 'Direktori pendakwah yang ditauliahkan oleh agensi agama negeri.', 'go' => 'Lihat direktori'],
            ];
        @endphp
        <div class="row g-4">
            @foreach($features as $f)
                <div class="col-sm-6 col-lg-3">
                    <a href="{{ route($f['route']) }}" class="surface lift feature reveal" style="--d: {{ $loop->index * .08 }}s">
                        <div class="ico ico-{{ $f['tone'] }}"><i class="bi bi-{{ $f['icon'] }}"></i></div>
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
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="section-title reveal">
                    <div>
                        <div class="eyebrow">Liputan</div>
                        <h2>Fatwa mengikut negeri</h2>
                    </div>
                    <a href="{{ route('fatwas.compare') }}" class="btn btn-outline-primary btn-sm">Bandingkan <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="surface p-4 reveal">
                    @php($max = max(1, $perState->max() ?? 1))
                    @forelse($perState as $state => $count)
                        <a class="bar-row" href="{{ route('fatwas.index', ['state' => $state]) }}">
                            <x-state-pill :state="$state" />
                            <div class="bar-track"><div class="bar-fill" data-width="{{ round($count / $max * 100) }}"></div></div>
                            <span class="count">{{ $count }}</span>
                        </a>
                    @empty
                        <div class="empty py-4"><i class="bi bi-database"></i>Belum ada fatwa. Jalankan <code>php artisan fatwa:scrape</code>.</div>
                    @endforelse
                    <p class="small text-muted mb-0 mt-3">
                        <i class="bi bi-info-circle"></i> Sumber: laman rasmi Jabatan Mufti Wilayah Persekutuan, Selangor, Negeri Sembilan dan Perak.
                    </p>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="section-title reveal">
                    <div>
                        <div class="eyebrow">Hari ini</div>
                        <h2>Waktu solat</h2>
                    </div>
                </div>
                <div class="reveal mb-5">@include('partials.prayer-times')</div>

                <div class="section-title reveal">
                    <div>
                        <div class="eyebrow">Topik</div>
                        <h2>Kategori</h2>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 reveal">
                    @forelse($categories as $category)
                        <a href="{{ route('fatwas.index', ['category' => $category->id]) }}" class="chip chip-light">
                            {{ $category->category_name }} <span class="badge rounded-pill text-bg-light border">{{ $category->fatwas_count }}</span>
                        </a>
                    @empty
                        <span class="text-muted">Tiada kategori lagi.</span>
                    @endforelse
                </div>

                <div class="surface p-4 mt-4 reveal">
                    <h3 class="h5 display-font">Apakah itu fatwa?</h3>
                    <p class="text-muted small mb-0">
                        Fatwa ialah keputusan hukum rasmi oleh pihak berautoriti Islam sebagai panduan masyarakat dalam hal akidah,
                        ibadah, muamalat dan kehidupan seharian. Di Malaysia, setiap negeri mempunyai Jawatankuasa Fatwa sendiri,
                        dan fatwa yang <strong>diwartakan</strong> mengikat umat Islam di negeri tersebut.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== Latest ========== --}}
<section class="section">
    <div class="container-xl">
        <div class="section-title reveal">
            <div>
                <div class="eyebrow">Terkini</div>
                <h2>Fatwa terbaharu</h2>
            </div>
            <a href="{{ route('fatwas.index') }}" class="btn btn-outline-primary btn-sm">Semua fatwa <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            @forelse($latest as $fatwa)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('fatwas.show', $fatwa) }}" class="surface lift d-flex flex-column h-100 p-4 text-decoration-none text-body reveal" style="--d: {{ $loop->index * .06 }}s">
                        <div class="meta-row mt-0 mb-2">
                            <x-state-pill :state="$fatwa->authority->state ?? null" />
                            @if($fatwa->status === 'Diwartakan')<span class="tag tag-gold"><i class="bi bi-patch-check"></i> Diwartakan</span>@endif
                        </div>
                        <h3 class="h6 fw-bold lh-base flex-grow-1" style="font-family: var(--ef-font)">{{ \Illuminate\Support\Str::limit($fatwa->title, 120) }}</h3>
                        <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                            <span><i class="bi bi-calendar3"></i> {{ $fatwa->date_issued ?? $fatwa->issued_year }}</span>
                            <span class="text-primary fw-semibold">Baca <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="empty"><i class="bi bi-journal"></i>Belum ada fatwa.</div></div>
            @endforelse
        </div>

        <div class="surface p-4 p-md-5 mt-5 text-center reveal" style="background: var(--ef-star-ink), var(--ef-surface)">
            <h2 class="h3">Ada cadangan untuk e-Fatwa?</h2>
            <p class="text-muted mx-auto" style="max-width: 560px">Kongsi pandangan anda atau laporkan masalah. Setiap maklum balas membantu kami menambah baik sistem ini.</p>
            <a href="{{ route('feedbacks.index') }}" class="btn btn-primary btn-lg"><i class="bi bi-chat-heart"></i> Hantar Maklum Balas</a>
        </div>
    </div>
</section>
@endsection
