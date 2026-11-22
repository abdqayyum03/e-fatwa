@extends('layouts.app')

@section('title', $lecture->title)

@section('content')
@include('partials.page-hero', [
    'title' => $lecture->title,
    'subtitle' => $lecture->type.' · '.$lecture->starts_at->locale('ms')->translatedFormat('l, j F Y, g:i A'),
    'icon' => 'calendar-event',
    'crumbs' => ['Kuliah' => route('lectures.index'), $lecture->title => null],
])

<div class="container-xl page-body">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="surface p-4">
                <div class="d-flex gap-3 align-items-center mb-4">
                    <div class="date-badge">
                        <div class="m">{{ $lecture->starts_at->locale('ms')->translatedFormat('M') }}</div>
                        <div class="d">{{ $lecture->starts_at->format('j') }}</div>
                        <div class="w">{{ $lecture->starts_at->format('Y') }}</div>
                    </div>
                    <div>
                        <span class="tag tag-gold">{{ $lecture->type }}</span>
                        @if($lecture->starts_at->isPast() && (! $lecture->ends_at || $lecture->ends_at->isPast()))
                            <span class="tag">Telah berlalu</span>
                        @else
                            <span class="tag"><i class="bi bi-clock"></i> {{ $lecture->starts_at->locale('ms')->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>

                @if($lecture->description)
                    <div style="white-space: pre-line; line-height: 1.8">{{ $lecture->description }}</div>
                @else
                    <p class="text-muted mb-0">Tiada penerangan tambahan.</p>
                @endif
            </div>

            @if($more->isNotEmpty())
                <h2 class="h5 mt-4 mb-3">Kuliah lain akan datang</h2>
                <div class="surface overflow-hidden">
                    @foreach($more as $other)
                        <a href="{{ route('lectures.show', $other) }}" class="fatwa-item align-items-center">
                            <div class="date-badge">
                                <div class="m">{{ $other->starts_at->locale('ms')->translatedFormat('M') }}</div>
                                <div class="d">{{ $other->starts_at->format('j') }}</div>
                                <div class="w">{{ $other->starts_at->format('g:i A') }}</div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="title">{{ $other->title }}</div>
                                <div class="meta-row"><span class="tag">{{ $other->place() }}</span></div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="sticky-side d-grid gap-3">
                <div class="surface p-4">
                    <ul class="info-list">
                        <li><i class="bi bi-clock"></i><div><small>Masa</small>
                            {{ $lecture->starts_at->format('g:i A') }}@if($lecture->ends_at) – {{ $lecture->ends_at->format('g:i A') }}@endif
                        </div></li>
                        <li><i class="bi bi-geo-alt"></i><div><small>Tempat</small>
                            {{ $lecture->place() ?? '-' }}
                            @if($lecture->mosque)<div class="small text-muted">{{ $lecture->mosque->location }}</div>@endif
                        </div></li>
                        <li><i class="bi bi-person"></i><div><small>Penceramah</small>
                            @if($lecture->preacher)
                                <a href="{{ route('preachers.show', $lecture->preacher) }}">{{ $lecture->preacher->preacher_name }}</a>
                            @else - @endif
                        </div></li>
                    </ul>
                </div>
                <div class="surface p-3 d-grid gap-2">
                    <a href="{{ route('lectures.ics', $lecture) }}" class="btn btn-primary"><i class="bi bi-calendar-plus"></i> Tambah ke kalendar</a>
                    @if($lecture->mosque && $lecture->mosque->latitude)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $lecture->mosque->latitude }},{{ $lecture->mosque->longitude }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                            <i class="bi bi-sign-turn-right"></i> Dapatkan arah
                        </a>
                    @endif
                    <button type="button" class="btn btn-ghost" data-copy><i class="bi bi-share"></i> Salin pautan</button>
                </div>
                @can('admin')
                    <div class="surface p-3 d-flex gap-2">
                        <a href="{{ route('lectures.edit', $lecture) }}" class="btn btn-outline-primary flex-fill"><i class="bi bi-pencil"></i> Sunting</a>
                        <form action="{{ route('lectures.destroy', $lecture) }}" method="POST" class="flex-fill" onsubmit="return confirm('Padam kuliah ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-trash"></i> Padam</button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
