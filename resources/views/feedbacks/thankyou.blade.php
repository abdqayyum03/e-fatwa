@extends('layouts.app')

@section('content')
<div class="p-5 rounded shadow-lg text-white position-relative overflow-hidden" style="
    background: url('{{ asset('images/fatwa-bg.jpg') }}') no-repeat center center;
    background-size: cover;
">
    <div class="container py-5" style="background-color: rgba(248, 249, 250, 0.75); border-radius: 15px;">
        <div class="text-center">
            <h2 class="fw-bold text-success">Maklum Balas Diterima</h2>
            <p class="mt-3 text-muted">
                Terima kasih kerana menghantar maklum balas anda. Pasukan pentadbir kami akan menyemaknya secepat mungkin.
            </p>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary mt-4">
                Kembali ke Laman Utama
            </a>
        </div>
    </div>
</div>
@endsection
