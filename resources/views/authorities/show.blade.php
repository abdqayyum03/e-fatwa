@extends('layouts.app')

@section('title', $authority->authority_name)

@section('hero-actions')
    @if($authority->fatwas_count)
        <a href="{{ route('fatwas.index', ['state' => $authority->state]) }}" class="btn btn-gold"><i class="bi bi-journal-text"></i> {{ $authority->fatwas_count }} fatwa</a>
    @endif
@endsection

@section('content')
@include('partials.page-hero', [
    'title' => $authority->authority_name,
    'subtitle' => $authority->state,
    'icon' => 'bank',
    'crumbs' => [__('Agensi') => route('authorities.index'), $authority->authority_name => null],
])

<div class="container-xl page-body">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="surface overflow-hidden sticky-side">
                <div class="logo-box" style="height: 160px">
                    @if($authority->logo && file_exists(public_path('images/authorities/' . $authority->logo)))
                        <img src="{{ asset('images/authorities/' . $authority->logo) }}" alt="Logo {{ $authority->authority_name }}" style="max-height: 120px">
                    @else
                        <div class="mono" style="--h: {{ crc32($authority->state ?? $authority->authority_name) % 360 }}">
                            {{ \Illuminate\Support\Str::of($authority->state ?? $authority->authority_name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('') }}
                        </div>
                    @endif
                </div>
                <div class="p-4">
                    <ul class="info-list">
                        @if($authority->street || $authority->city)
                            <li><i class="bi bi-geo-alt"></i><div><small>{{ __('Alamat') }}</small>
                                {{ collect([$authority->street, trim($authority->poscode . ' ' . $authority->city), $authority->state])->filter()->join(', ') }}
                            </div></li>
                        @endif
                        @if($authority->contact_info)
                            <li><i class="bi bi-telephone"></i><div><small>{{ __('Hubungi') }}</small>{{ $authority->contact_info }}</div></li>
                        @endif
                        <li><i class="bi bi-journal-text"></i><div><small>{{ __('Fatwa dalam pangkalan data') }}</small>{{ $authority->fatwas_count }}</div></li>
                    </ul>
                    @if($authority->street)
                        <a class="btn btn-outline-primary w-100 mt-3" target="_blank" rel="noopener"
                           href="https://www.google.com/maps/search/?api=1&query={{ urlencode($authority->authority_name . ' ' . $authority->street . ' ' . $authority->city) }}">
                            <i class="bi bi-map"></i> {{ __('Lihat di peta') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if($byYear->isNotEmpty())
                @php($max = max(1, $byYear->max()))
                <div class="surface p-4 mb-4 reveal">
                    <h2 class="h5 mb-3">{{ __('Fatwa mengikut tahun') }}</h2>
                    <div class="d-flex align-items-end gap-2" style="height: 140px">
                        @foreach($byYear->sortKeys() as $year => $total)
                            <a href="{{ route('fatwas.index', ['state' => $authority->state, 'year' => $year]) }}" class="flex-fill text-center text-decoration-none"
                               data-bs-toggle="tooltip" title="{{ $year }}: {{ $total }} fatwa">
                                <div class="rounded-top mx-auto" style="height: {{ max(6, round($total / $max * 110)) }}px; background: linear-gradient(180deg, var(--ef-gold), var(--ef-primary)); max-width: 34px"></div>
                                <small class="text-muted d-block mt-1" style="font-size: .7rem">{{ $year }}</small>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-end mb-3">
                <h2 class="h4 mb-0">{{ __('Fatwa terkini') }}</h2>
                @if($authority->fatwas_count > $latest->count())
                    <a href="{{ route('fatwas.index', ['state' => $authority->state]) }}" class="small fw-semibold">{{ __('Semua :count', ['count' => $authority->fatwas_count]) }} <i class="bi bi-arrow-right"></i></a>
                @endif
            </div>
            <div class="surface overflow-hidden">
                @forelse($latest as $fatwa)
                    <a href="{{ route('fatwas.show', $fatwa) }}" class="fatwa-item">
                        <div class="year"><b>{{ $fatwa->issued_year ?? '–' }}</b><small>{{ __('Tahun') }}</small></div>
                        <div class="flex-grow-1">
                            <div class="title">{{ $fatwa->title }}</div>
                            <div class="meta-row">
                                @if($fatwa->category)<span class="tag"><i class="bi bi-tag"></i> {{ $fatwa->category->category_name }}</span>@endif
                                @if($fatwa->gazette_no)<span class="tag">{{ $fatwa->gazette_no }}</span>@endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="empty"><i class="bi bi-journal"></i>{{ __('Belum ada fatwa daripada agensi ini dalam pangkalan data.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
