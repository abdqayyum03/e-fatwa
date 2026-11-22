@extends('layouts.app')

@section('title', __('Senarai Fatwa'))

@section('hero-actions')
    <a href="{{ route('fatwas.compare', array_filter(['search' => $filters['search'] ?? null])) }}" class="btn btn-light"><i class="bi bi-columns-gap"></i> {{ __('Bandingkan negeri') }}</a>
    @can('admin')
        <a href="{{ route('fatwas.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg"></i> {{ __('Tambah') }}</a>
    @endcan
@endsection

@section('content')
@include('partials.page-hero', [
    'title' => __('Senarai Fatwa'),
    'subtitle' => __('Carian teks penuh merentas fatwa daripada semua negeri. Tapis mengikut negeri, tahun dan kategori.'),
    'icon' => 'journal-text',
    'crumbs' => [__('Fatwa') => route('fatwas.index')],
])

<div class="container-xl page-body">
    {{-- Filters (dropdowns apply immediately) --}}
    <form method="GET" action="{{ route('fatwas.index') }}" class="surface filter-bar mb-4" data-autosubmit>
        <div class="row g-2 align-items-center">
            <div class="col-lg-5">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control ps-5"
                           placeholder="{{ __('Cari dalam tajuk dan teks fatwa...') }}" aria-label="{{ __('Kata kunci') }}" data-primary-search>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <select name="state" class="form-select" aria-label="{{ __('Negeri') }}">
                    <option value="">{{ __('Semua negeri') }}</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" @selected(($filters['state'] ?? '') === $state)>{{ $state }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <select name="year" class="form-select" aria-label="{{ __('Tahun') }}">
                    <option value="">{{ __('Semua tahun') }}</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-8 col-md-4 col-lg-2">
                <select name="category" class="form-select" aria-label="{{ __('Kategori') }}">
                    <option value="">{{ __('Semua kategori') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-2 col-lg-1 d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i><span class="d-lg-none ms-1">{{ __('Cari') }}</span></button>
            </div>
        </div>

        @if(array_filter($filters))
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
                <span class="small text-muted">{{ __('Tapisan:') }}</span>
                @foreach(array_filter($filters) as $key => $value)
                    @php
                        $label = match ($key) {
                            'category' => optional($categories->firstWhere('id', $value))->category_name,
                            'agency' => optional($agencies->firstWhere('id', $value))->authority_name,
                            default => $value,
                        };
                    @endphp
                    <a href="{{ route('fatwas.index', \Illuminate\Support\Arr::except(array_filter($filters), $key)) }}" class="chip chip-light py-1">
                        {{ $label }} <i class="bi bi-x"></i>
                    </a>
                @endforeach
                <a href="{{ route('fatwas.index') }}" class="small ms-1">{{ __('Kosongkan semua') }}</a>
            </div>
        @endif
    </form>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="result-count"><strong class="text-body">{{ number_format($fatwas->total()) }}</strong> {{ __('fatwa dijumpai') }}</span>
        @if($fatwas->hasPages())
            <span class="small text-muted">{{ __('Halaman :page / :last', ['page' => $fatwas->currentPage(), 'last' => $fatwas->lastPage()]) }}</span>
        @endif
    </div>

    <div class="surface overflow-hidden">
        @forelse($fatwas as $fatwa)
            @php
                $snippet = null;
                if (! empty($filters['search']) && $fatwa->full_text) {
                    $pos = mb_stripos($fatwa->full_text, $filters['search']);
                    if ($pos !== false) {
                        $raw = mb_substr($fatwa->full_text, max(0, $pos - 90), 240);
                        $snippet = preg_replace('/(' . preg_quote(e($filters['search']), '/') . ')/iu', '<mark>$1</mark>', e(preg_replace('/\s+/u', ' ', $raw)));
                    }
                }
            @endphp
            <a href="{{ route('fatwas.show', $fatwa) }}" class="fatwa-item">
                <div class="year">
                    <b>{{ $fatwa->issued_year ?? '–' }}</b>
                    <small>{{ __('Tahun') }}</small>
                </div>
                <div class="flex-grow-1" style="min-width: 0">
                    <div class="title">{{ $fatwa->title }}</div>
                    @if($snippet)
                        <div class="snippet">…{!! $snippet !!}…</div>
                    @endif
                    <div class="meta-row">
                        <x-state-pill :state="$fatwa->authority->state ?? null" />
                        @if($fatwa->category)<span class="tag"><i class="bi bi-tag"></i> {{ $fatwa->category->category_name }}</span>@endif
                        @if($fatwa->status === 'Diwartakan')<span class="tag tag-gold"><i class="bi bi-patch-check"></i> {{ __('Diwartakan') }}</span>@endif
                        @if($fatwa->gazette_no)<span class="tag">{{ $fatwa->gazette_no }}</span>@endif
                        @if($fatwa->full_text)
                            <span class="tag" title="{{ __('Teks penuh tersedia') }}"><i class="bi bi-file-text"></i> {{ __('Teks') }}</span>
                        @elseif($fatwa->pdf_url)
                            <span class="tag" title="{{ __('PDF sahaja') }}"><i class="bi bi-file-earmark-pdf"></i> PDF</span>
                        @endif
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted align-self-center d-none d-md-block"></i>
            </a>
        @empty
            <div class="empty">
                <i class="bi bi-search"></i>
                <p class="mb-2">{{ __('Tiada fatwa yang sepadan dengan carian anda.') }}</p>
                <a href="{{ route('fatwas.index') }}" class="btn btn-outline-primary btn-sm">{{ __('Kosongkan tapisan') }}</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $fatwas->onEachSide(1)->links() }}
    </div>
</div>
@endsection
