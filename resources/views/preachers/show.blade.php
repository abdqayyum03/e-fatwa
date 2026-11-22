@extends('layouts.app')

@section('title', $preacher->preacher_name)

@section('content')
@include('partials.page-hero', [
    'title' => $preacher->preacher_name,
    'subtitle' => collect([$preacher->location, $preacher->state])->filter()->join(', '),
    'icon' => 'person-badge',
    'crumbs' => ['Pendakwah' => route('preachers.index'), $preacher->preacher_name => null],
])

<div class="container-xl page-body">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="surface p-4 text-center">
                @php($initials = \Illuminate\Support\Str::of($preacher->preacher_name)->replaceMatches('/^(ustaz|ustazah|dr\.?|haji|hajah|tuan|puan)\s+/i', '')->explode(' ')->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join(''))
                <div class="avatar mx-auto mb-3" style="--h: {{ crc32($preacher->preacher_name) % 360 }}; width: 88px; height: 88px; font-size: 1.8rem">{{ $initials }}</div>
                <h2 class="h5 mb-2" style="font-family: var(--ef-font)">{{ $preacher->preacher_name }}</h2>
                <x-state-pill :state="$preacher->state" />
                <span class="tag tag-gold ms-1"><i class="bi bi-patch-check"></i> Bertauliah</span>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="surface p-4">
                <ul class="info-list">
                    <li><i class="bi bi-geo-alt"></i><div><small>Lokasi</small>{{ $preacher->location ?: '-' }}</div></li>
                    <li><i class="bi bi-map"></i><div><small>Negeri</small>{{ $preacher->state ?: '-' }}</div></li>
                    <li><i class="bi bi-bank"></i><div><small>Agensi penauliah</small>
                        @if($preacher->authority)
                            <a href="{{ route('authorities.show', $preacher->authority) }}">{{ $preacher->authority->authority_name }}</a>
                        @else - @endif
                    </div></li>
                    <li><i class="bi bi-award"></i><div><small>Butiran tauliah</small>{!! nl2br(e($preacher->certification_details ?: '-')) !!}</div></li>
                </ul>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('preachers.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Kembali ke direktori</a>
                @can('admin')
                    <a href="{{ route('preachers.edit', $preacher) }}" class="btn btn-outline-primary ms-auto"><i class="bi bi-pencil"></i> Sunting</a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
