@extends('layouts.app')

@section('title', __('Terima Kasih'))

@section('content')
<div class="auth-wrap">
    <div class="surface p-5 text-center reveal" style="max-width: 520px">
        <div class="mx-auto mb-4 d-grid rounded-circle ico-emerald" style="width: 84px; height: 84px; place-items: center; font-size: 2.4rem">
            <i class="bi bi-check2-circle"></i>
        </div>
        <h1 class="h3">{{ __('Maklum balas diterima') }}</h1>
        <p class="text-muted">{{ __('Terima kasih kerana meluangkan masa. Pasukan kami akan menyemak maklum balas anda secepat mungkin.') }}</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
            <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-house"></i> {{ __('Laman Utama') }}</a>
            <a href="{{ route('fatwas.index') }}" class="btn btn-outline-primary">{{ __('Teroka Fatwa') }}</a>
        </div>
    </div>
</div>
@endsection
