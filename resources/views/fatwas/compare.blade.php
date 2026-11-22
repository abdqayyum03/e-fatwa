@extends('layouts.app')

@section('title', __('Bandingkan Fatwa Antara Negeri'))

@section('content')
@include('partials.page-hero', [
    'title' => __('Bandingkan Fatwa Antara Negeri'),
    'subtitle' => __('Setiap negeri mengeluarkan fatwanya sendiri. Pilih satu isu untuk melihat keputusan setiap negeri secara bersebelahan.'),
    'icon' => 'columns-gap',
    'crumbs' => [__('Bandingkan') => route('fatwas.compare')],
])

<div class="container-xl page-body">
    <form method="GET" action="{{ route('fatwas.compare') }}" class="surface filter-bar mb-3" data-autosubmit>
        <div class="row g-2 align-items-center">
            <div class="col-lg-6">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control ps-5"
                           placeholder="{{ __('Isu atau kata kunci, cth. zakat') }}" aria-label="{{ __('Isu') }}" data-primary-search>
                </div>
            </div>
            <div class="col-md-8 col-lg-4">
                <select name="category" class="form-select" aria-label="{{ __('Kategori') }}">
                    <option value="">{{ __('atau pilih kategori') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-2 d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-columns-gap"></i> {{ __('Bandingkan') }}</button>
            </div>
        </div>
    </form>

    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
        <span class="small text-muted">{{ __('Cuba:') }}</span>
        @foreach(['zakat', 'ahli sunnah', 'ajaran', 'wakaf', 'faraid', 'syiah'] as $topic)
            <a href="{{ route('fatwas.compare', ['search' => $topic]) }}"
               class="chip chip-light py-1 @if(($filters['search'] ?? '') === $topic) border-primary text-primary @endif">{{ $topic }}</a>
        @endforeach
    </div>

    @if(! array_filter($filters))
        <div class="surface empty">
            <i class="bi bi-columns-gap"></i>
            <p class="mb-1">{{ __('Masukkan isu atau pilih kategori untuk mula membandingkan.') }}</p>
            <p class="small mb-0">{{ __('Negeri dengan data:') }}
                @foreach($statesWithFatwas as $state) <x-state-pill :state="$state" class="ms-1" /> @endforeach
            </p>
        </div>
    @elseif($columns->isEmpty())
        <div class="surface empty">
            <i class="bi bi-search"></i>
            {{ __('Tiada fatwa dijumpai untuk carian ini di mana-mana negeri.') }}
        </div>
    @else
        @php($missing = $statesWithFatwas->diff($columns->keys()))

        <p class="result-count mb-3">
            {!! __('Ditemui dalam :states negeri · :count fatwa', ['states' => '<strong class="text-body">'.$columns->count().'</strong>', 'count' => '<strong class="text-body">'.$columns->sum(fn ($c) => $c->count()).'</strong>']) !!}
            @if($missing->isNotEmpty())
                · {{ __('tiada daripada :states', ['states' => $missing->join(', ')]) }}
            @endif
        </p>

        <div class="row flex-nowrap overflow-auto g-3 pb-3 align-items-start compare-scroller">
            @foreach($columns as $state => $fatwas)
                <div class="col-11 col-md-6 col-xl-4 compare-col reveal" style="--d: {{ $loop->index * .08 }}s">
                    <div class="card overflow-hidden">
                        <div class="card-header d-flex justify-content-between align-items-center py-3">
                            <strong class="display-font fs-5">{{ $state }}</strong>
                            <span class="badge rounded-pill bg-white text-dark">{{ $fatwas->count() }} fatwa</span>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($fatwas->take(15) as $fatwa)
                                <a href="{{ route('fatwas.show', $fatwa) }}" class="list-group-item list-group-item-action py-3">
                                    <div class="fw-semibold small lh-sm">{{ $fatwa->title }}</div>
                                    <div class="meta-row">
                                        <span class="tag"><i class="bi bi-calendar3"></i> {{ $fatwa->date_issued ?? $fatwa->issued_year }}</span>
                                        @if($fatwa->gazette_no)<span class="tag">{{ $fatwa->gazette_no }}</span>@endif
                                        @if($fatwa->status === 'Diwartakan')<span class="tag tag-gold">{{ __('Diwartakan') }}</span>@endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        @if($fatwas->count() > 15)
                            <div class="card-footer text-center bg-transparent">
                                <a href="{{ route('fatwas.index', array_filter($filters) + ['state' => $state]) }}" class="small fw-semibold">
                                    {{ __('Lihat kesemua :count fatwa', ['count' => $fatwas->count()]) }} <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
