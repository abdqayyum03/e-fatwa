@extends('layouts.app')

@section('title', __('Langganan dihentikan'))

@section('content')
<div class="auth-wrap">
    <div class="surface p-5 text-center reveal" style="max-width: 520px">
        <div class="mx-auto mb-4 d-grid rounded-circle ico-emerald" style="width: 84px; height: 84px; place-items: center; font-size: 2.2rem">
            <i class="bi bi-bell-slash"></i>
        </div>
        <h1 class="h3">{{ __('Langganan dihentikan') }}</h1>
        <p class="text-muted">{{ __(':email tidak akan menerima makluman fatwa baharu lagi.', ['email' => $user->email]) }}</p>
        <p class="small text-muted">{{ __('Anda boleh melanggan semula pada bila-bila masa di halaman akaun anda.') }}</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
            <a href="{{ route('account.edit') }}" class="btn btn-outline-primary">{{ __('Tetapan makluman') }}</a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">{{ __('Laman Utama') }}</a>
        </div>
    </div>
</div>
@endsection
