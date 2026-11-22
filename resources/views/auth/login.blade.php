@extends('layouts.app')

@section('title', 'Log Masuk Admin')

@section('content')
<div class="auth-wrap">
    <div class="surface overflow-hidden w-100 reveal" style="max-width: 420px">
        <div class="page-hero text-center" style="margin: 0; padding: 2.2rem 1.5rem">
            <div class="hero-icon mx-auto mb-3"><i class="bi bi-shield-lock"></i></div>
            <h1 class="h3">Log Masuk Admin</h1>
            <p class="mx-auto small">Untuk menambah dan mengemas kini fatwa, pendakwah dan masjid.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="p-4">
            @csrf

            <div class="form-floating mb-3">
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="E-mel"
                       class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                <label for="email">E-mel</label>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating mb-3 position-relative">
                <input type="password" id="password" name="password" class="form-control pe-5" placeholder="Kata laluan" required autocomplete="current-password">
                <label for="password">Kata laluan</label>
                <button type="button" class="btn btn-ghost btn-sm position-absolute top-50 end-0 translate-middle-y me-2" id="togglePw" aria-label="Tunjuk kata laluan">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Log Masuk</button>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost w-100 mt-2">Kembali ke laman utama</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.getElementById('togglePw').addEventListener('click', function () {
            const pw = document.getElementById('password');
            const show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            this.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            this.setAttribute('aria-label', show ? 'Sembunyi kata laluan' : 'Tunjuk kata laluan');
        });
    </script>
@endpush
