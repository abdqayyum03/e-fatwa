@extends('layouts.app')

@section('content')
<div class="container-fluid py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Senarai Agensi Penguatkuasa Agama</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="row g-4">
        @forelse($authorities as $authority)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="{{ route('authorities.show', $authority->id) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm border-0 hover-shadow">
                        @if($authority->logo)
                            <img src="{{ asset('images/authorities/' . $authority->logo) }}" class="card-img-top p-3" alt="{{ $authority->authority_name }}">
                        @endif
                        <div class="card-body text-center">
                            <h5 class="card-title text-dark fw-bold">{{ $authority->authority_name }}</h5>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center text-muted">
                Tiada agensi dijumpai.
            </div>
        @endforelse
    </div>
</div>
@endsection
