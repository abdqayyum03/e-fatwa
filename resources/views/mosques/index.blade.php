@extends('layouts.app')

@section('title', __('Senarai Masjid'))

@section('hero-actions')
    <a href="{{ route('mosques.map') }}" class="btn btn-light"><i class="bi bi-map"></i> {{ __('Lihat peta') }}</a>
    @can('admin')
        <a href="{{ route('mosques.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg"></i> {{ __('Tambah') }}</a>
    @endcan
@endsection

@section('content')
@include('partials.page-hero', [
    'title' => __('Senarai Masjid'),
    'subtitle' => __('Cari masjid mengikut nama atau lokasi.'),
    'icon' => 'building',
    'crumbs' => [__('Masjid') => route('mosques.index')],
])

<div class="container-xl page-body">
    <form method="GET" action="{{ route('mosques.index') }}" class="surface filter-bar mb-4" data-autosubmit>
        <div class="row g-2">
            <div class="col-md-7">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control ps-5" placeholder="{{ __('Nama atau lokasi masjid') }}" aria-label="{{ __('Cari masjid') }}" data-primary-search>
                </div>
            </div>
            <div class="col-8 col-md-4">
                <select name="location" class="form-select" aria-label="{{ __('Lokasi') }}">
                    <option value="">{{ __('Semua lokasi') }}</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" @selected(request('location') == $loc)>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-1 d-grid">
                <button type="submit" class="btn btn-primary" aria-label="{{ __('Cari') }}"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </form>

    <div class="row g-4">
        @forelse($mosques as $mosque)
            @php
                $mapQuery = ($mosque->latitude && $mosque->longitude)
                    ? $mosque->latitude . ',' . $mosque->longitude
                    : $mosque->name . ' ' . $mosque->location;
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="surface lift p-4 h-100 d-flex flex-column reveal" style="--d: {{ ($loop->index % 6) * .05 }}s">
                    <div class="d-flex gap-3 align-items-start mb-3">
                        <div class="ico ico-emerald rounded-3 d-grid flex-shrink-0" style="width: 46px; height: 46px; place-items: center; font-size: 1.2rem"><i class="bi bi-moon-stars"></i></div>
                        <div style="min-width: 0">
                            <h2 class="h6 fw-bold mb-1" style="font-family: var(--ef-font)">{{ $mosque->name }}</h2>
                            <div class="small text-muted"><i class="bi bi-geo-alt"></i> {{ $mosque->location }}</div>
                            @if($mosque->contact_info)
                                <div class="small text-muted"><i class="bi bi-telephone"></i> {{ $mosque->contact_info }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="mt-auto d-flex gap-2">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($mapQuery) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm flex-fill">
                            <i class="bi bi-sign-turn-right"></i> {{ __('Arah') }}
                        </a>
                        @can('admin')
                            <a href="{{ route('mosques.edit', $mosque) }}" class="btn btn-ghost btn-sm" aria-label="{{ __('Sunting') }}"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('mosques.destroy', $mosque) }}" method="POST" onsubmit="return confirm('{{ __('Padam masjid ini?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-danger" aria-label="{{ __('Padam') }}"><i class="bi bi-trash"></i></button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="surface empty">
                    <i class="bi bi-building"></i>
                    @if(request()->hasAny(['search', 'location']) && (request('search') || request('location')))
                        {{ __('Tiada masjid sepadan dengan carian.') }} <a href="{{ route('mosques.index') }}">{{ __('Kosongkan tapisan') }}</a>
                    @else
                        {{ __('Belum ada masjid dalam senarai.') }}
                        @can('admin') <div class="mt-3"><a href="{{ route('mosques.create') }}" class="btn btn-primary btn-sm">{{ __('Tambah masjid pertama') }}</a></div> @endcan
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">{{ $mosques->withQueryString()->links() }}</div>
</div>
@endsection
