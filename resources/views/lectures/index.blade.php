@extends('layouts.app')

@section('title', 'Jadual Kuliah')

@section('hero-actions')
    @can('admin')
        <a href="{{ route('lectures.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg"></i> Tambah</a>
    @endcan
@endsection

@section('content')
@include('partials.page-hero', [
    'title' => 'Jadual Kuliah',
    'subtitle' => 'Kuliah, ceramah dan majlis ilmu di masjid-masjid dalam direktori.',
    'icon' => 'calendar-event',
    'crumbs' => ['Kuliah' => route('lectures.index')],
])

<div class="container-xl page-body">
    <form method="GET" action="{{ route('lectures.index') }}" class="surface filter-bar mb-4" data-autosubmit>
        @if($past)<input type="hidden" name="past" value="1">@endif
        <div class="row g-2">
            <div class="col-md-4">
                <select name="mosque" class="form-select" aria-label="Masjid">
                    <option value="">Semua masjid</option>
                    @foreach($mosques as $mosque)
                        <option value="{{ $mosque->id }}" @selected(($filters['mosque'] ?? '') == $mosque->id)>{{ $mosque->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="preacher" class="form-select" aria-label="Penceramah">
                    <option value="">Semua penceramah</option>
                    @foreach($preachers as $preacher)
                        <option value="{{ $preacher->id }}" @selected(($filters['preacher'] ?? '') == $preacher->id)>{{ $preacher->preacher_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="type" class="form-select" aria-label="Jenis">
                    <option value="">Semua jenis</option>
                    @foreach(\App\Models\Lecture::TYPES as $type)
                        <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <ul class="nav nav-pills mb-3">
        <li class="nav-item"><a class="nav-link @unless($past) active @endunless" href="{{ route('lectures.index', \Illuminate\Support\Arr::except(array_filter($filters), 'past')) }}">Akan datang</a></li>
        <li class="nav-item"><a class="nav-link @if($past) active @endif" href="{{ route('lectures.index', array_filter($filters) + ['past' => 1]) }}">Lepas</a></li>
    </ul>

    @forelse($days as $date => $items)
        @php($day = \Illuminate\Support\Carbon::parse($date)->locale('ms'))
        <h2 class="h6 text-muted text-uppercase mt-4 mb-2" style="letter-spacing: .08em">
            @if($day->isToday()) Hari ini · @elseif($day->isTomorrow()) Esok · @endif
            {{ $day->translatedFormat('l, j F Y') }}
        </h2>
        <div class="surface overflow-hidden mb-3">
            @foreach($items as $lecture)
                <a href="{{ route('lectures.show', $lecture) }}" class="fatwa-item align-items-center">
                    <div class="date-badge">
                        <div class="m">{{ $lecture->starts_at->locale('ms')->translatedFormat('M') }}</div>
                        <div class="d">{{ $lecture->starts_at->format('j') }}</div>
                        <div class="w">{{ $lecture->starts_at->format('g:i A') }}</div>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0">
                        <div class="title">{{ $lecture->title }}</div>
                        <div class="meta-row">
                            <span class="tag tag-gold">{{ $lecture->type }}</span>
                            @if($lecture->place())<span class="tag"><i class="bi bi-geo-alt"></i> {{ $lecture->place() }}</span>@endif
                            @if($lecture->preacher)<span class="tag"><i class="bi bi-person"></i> {{ $lecture->preacher->preacher_name }}</span>@endif
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted d-none d-md-block"></i>
                </a>
            @endforeach
        </div>
    @empty
        <div class="surface empty">
            <i class="bi bi-calendar-x"></i>
            <p class="mb-2">{{ $past ? 'Tiada kuliah lepas yang direkodkan.' : 'Tiada kuliah akan datang buat masa ini.' }}</p>
            @can('admin')
                <a href="{{ route('lectures.create') }}" class="btn btn-primary btn-sm">Tambah kuliah</a>
            @endcan
        </div>
    @endforelse

    <div class="mt-4 d-flex justify-content-center">{{ $lectures->links() }}</div>
</div>
@endsection
