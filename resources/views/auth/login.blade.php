@extends('layouts.app')

@section('title', __('Log Masuk'))

@section('content')
<div class="auth-wrap">
    <div class="surface overflow-hidden w-100 reveal" style="max-width: 420px">
        <div class="page-hero text-center" style="margin: 0; padding: 2.2rem 1.5rem">
            <div class="hero-icon mx-auto mb-3"><i class="bi bi-person-circle"></i></div>
            <h1 class="h3">{{ __('Log Masuk') }}</h1>
            <p class="mx-auto small">{{ __('Simpan fatwa, terima makluman fatwa baharu dan tanya soalan kepada pembantu AI.') }}</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="p-4">
            @csrf

            <div class="form-floating mb-3">
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('E-mel') }}"
                       class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                <label for="email">{{ __('E-mel') }}</label>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating mb-3 position-relative">
                <input type="password" id="password" name="password" class="form-control pe-5" placeholder="{{ __('Kata laluan') }}" required autocomplete="current-password">
                <label for="password">{{ __('Kata laluan') }}</label>
                <button type="button" class="btn btn-ghost btn-sm position-absolute top-50 end-0 translate-middle-y me-2" data-toggle-password="password" aria-label="{{ __('Tunjuk kata laluan') }}">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                <label for="remember" class="form-check-label">{{ __('Ingat saya') }}</label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('Log Masuk') }}</button>
            <p class="text-center small text-muted mt-3 mb-0">
                {{ __('Belum ada akaun?') }} <a href="{{ route('register') }}" class="fw-semibold">{{ __('Daftar sekarang') }}</a>
            </p>
        </form>
    </div>
</div>
@endsection
