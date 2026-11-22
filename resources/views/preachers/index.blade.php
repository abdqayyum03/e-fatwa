@extends('layouts.app')

@section('title', 'Pendakwah Bertauliah')

@section('hero-actions')
    @can('admin')
        <a href="{{ route('preachers.create') }}" class="btn btn-gold"><i class="bi bi-person-plus"></i> Tambah</a>
    @endcan
@endsection

@section('content')
@include('partials.page-hero', [
    'title' => 'Pendakwah Bertauliah',
    'subtitle' => 'Direktori pendakwah yang ditauliahkan oleh agensi agama negeri.',
    'icon' => 'people',
    'crumbs' => ['Pendakwah' => route('preachers.index')],
])

<div class="container-xl page-body">
    <form method="GET" action="{{ route('preachers.index') }}" class="surface filter-bar mb-4" data-autosubmit>
        <div class="row g-2">
            <div class="col-md-5">
                <div class="position-relative">
                    <i class="bi bi-person position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="search" name="name" class="form-control ps-5" placeholder="Nama pendakwah" value="{{ $filters['name'] ?? '' }}" aria-label="Nama" data-primary-search>
                </div>
            </div>
            <div class="col-md-3">
                <input type="search" name="location" class="form-control" placeholder="Lokasi" value="{{ $filters['location'] ?? '' }}" aria-label="Lokasi">
            </div>
            <div class="col-8 col-md-3">
                <select name="state" class="form-select" aria-label="Negeri">
                    <option value="">Semua negeri</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" @selected(($filters['state'] ?? '') === $state)>{{ $state }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-1 d-grid">
                <button type="submit" class="btn btn-primary" aria-label="Cari"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </form>

    <div class="row g-4">
        @forelse($preachers as $preacher)
            @php($initials = \Illuminate\Support\Str::of($preacher->preacher_name)->replaceMatches('/^(ustaz|ustazah|dr\.?|haji|hajah|tuan|puan)\s+/i', '')->explode(' ')->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join(''))
            <div class="col-md-6 col-xl-4">
                <div class="surface lift p-4 h-100 d-flex flex-column reveal" style="--d: {{ ($loop->index % 6) * .05 }}s">
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <div class="avatar" style="--h: {{ crc32($preacher->preacher_name) % 360 }}">{{ $initials }}</div>
                        <div style="min-width: 0">
                            <a href="{{ route('preachers.show', $preacher) }}" class="fw-bold text-body text-decoration-none">{{ $preacher->preacher_name }}</a>
                            <div class="small text-muted text-truncate"><i class="bi bi-geo-alt"></i> {{ $preacher->location ?: 'Lokasi tidak dinyatakan' }}</div>
                        </div>
                    </div>
                    <div class="meta-row mt-0 mb-3">
                        <x-state-pill :state="$preacher->state" />
                        @if($preacher->authority)<span class="tag"><i class="bi bi-bank"></i> {{ $preacher->authority->authority_name }}</span>@endif
                    </div>
                    <div class="mt-auto d-flex gap-2">
                        <a href="{{ route('preachers.show', $preacher) }}" class="btn btn-outline-primary btn-sm flex-fill">Profil</a>
                        @can('admin')
                            <a href="{{ route('preachers.edit', $preacher) }}" class="btn btn-ghost btn-sm" aria-label="Sunting"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('preachers.destroy', $preacher) }}" method="POST" onsubmit="return confirm('Padam pendakwah ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-danger" aria-label="Padam"><i class="bi bi-trash"></i></button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="surface empty">
                    <i class="bi bi-people"></i>
                    @if(array_filter($filters))
                        Tiada pendakwah sepadan dengan carian. <a href="{{ route('preachers.index') }}">Kosongkan tapisan</a>
                    @else
                        Belum ada pendakwah dalam direktori.
                        @can('admin') <div class="mt-3"><a href="{{ route('preachers.create') }}" class="btn btn-primary btn-sm">Tambah pendakwah pertama</a></div> @endcan
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">{{ $preachers->links() }}</div>
</div>
@endsection
