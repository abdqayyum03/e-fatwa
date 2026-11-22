@extends('layouts.app')

@section('title', __('Agensi Berautoriti'))

@section('content')
@include('partials.page-hero', [
    'title' => __('Agensi Berautoriti'),
    'subtitle' => __('Jabatan mufti persekutuan dan negeri yang mengeluarkan fatwa di Malaysia.'),
    'icon' => 'bank',
    'crumbs' => [__('Agensi') => route('authorities.index')],
])

<div class="container-xl page-body">
    <div class="row g-4">
        @forelse($authorities as $authority)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <a href="{{ route('authorities.show', $authority) }}" class="surface lift d-flex flex-column h-100 text-decoration-none text-body reveal" style="--d: {{ $loop->index * .06 }}s">
                    <div class="logo-box">
                        @if($authority->logo && file_exists(public_path('images/authorities/' . $authority->logo)))
                            <img src="{{ asset('images/authorities/' . $authority->logo) }}" alt="Logo {{ $authority->authority_name }}">
                        @else
                            <div class="mono" style="--h: {{ crc32($authority->state ?? $authority->authority_name) % 360 }}">
                                {{ \Illuminate\Support\Str::of($authority->state ?? $authority->authority_name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('') }}
                            </div>
                        @endif
                    </div>
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <x-state-pill :state="$authority->state" class="align-self-start mb-2" />
                        <h2 class="h5 mb-1" style="font-family: var(--ef-font)">{{ $authority->authority_name }}</h2>
                        <p class="small text-muted mb-3">{{ $authority->city }}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
                            <span><strong class="fs-5 display-font text-primary">{{ $authority->fatwas_count }}</strong> <span class="small text-muted">{{ __('fatwa') }}</span></span>
                            <span class="small text-primary fw-semibold">{{ __('Profil') }} <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12"><div class="surface empty"><i class="bi bi-bank"></i>{{ __('Tiada agensi dijumpai.') }}</div></div>
        @endforelse
    </div>
</div>
@endsection
