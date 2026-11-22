@extends('layouts.app')

@section('title', __('Daftar Akaun'))

@section('content')
<div class="auth-wrap">
    <div class="surface overflow-hidden w-100 reveal" style="max-width: 460px">
        <div class="page-hero text-center" style="margin: 0; padding: 2.2rem 1.5rem">
            <div class="hero-icon mx-auto mb-3"><i class="bi bi-person-plus"></i></div>
            <h1 class="h3">{{ __('Daftar Akaun') }}</h1>
            <p class="mx-auto small">{{ __('Percuma. Simpan fatwa kegemaran dan terima makluman mingguan tentang fatwa baharu.') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="p-4">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger small"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <div class="form-floating mb-3">
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('Nama') }}" class="form-control" required autofocus autocomplete="name">
                <label for="name">{{ __('Nama') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('E-mel') }}" class="form-control" required autocomplete="email">
                <label for="email">{{ __('E-mel') }}</label>
            </div>
            <div class="form-floating mb-1 position-relative">
                <input type="password" id="password" name="password" placeholder="{{ __('Kata laluan') }}" class="form-control pe-5" required autocomplete="new-password" minlength="8">
                <label for="password">{{ __('Kata laluan') }}</label>
                <button type="button" class="btn btn-ghost btn-sm position-absolute top-50 end-0 translate-middle-y me-2" data-toggle-password="password" aria-label="{{ __('Tunjuk kata laluan') }}">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <p class="form-text mb-3">{{ __('Sekurang-kurangnya 8 aksara, dengan huruf dan nombor.') }}</p>
            <div class="form-floating mb-4">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="{{ __('Sahkan kata laluan') }}" class="form-control" required autocomplete="new-password">
                <label for="password_confirmation">{{ __('Sahkan kata laluan') }}</label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('Daftar') }}</button>
            <p class="text-center small text-muted mt-3 mb-0">
                {{ __('Sudah ada akaun?') }} <a href="{{ route('login') }}" class="fw-semibold">{{ __('Log masuk') }}</a>
            </p>
        </form>
    </div>
</div>
@endsection
