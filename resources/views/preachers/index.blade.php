@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-center fw-bold">Senarai Agensi Penguatkuasa</h2>

    <div class="row g-4">
        @forelse($authorities as $authority)
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100 d-flex flex-row align-items-center p-3 hover-shadow">
                    <div class="flex-shrink-0">
                        <img src="{{ asset('storage/logos/' . $authority->logo) }}" alt="Logo {{ $authority->authority_name }}" class="rounded" style="width: 100px; height: 100px; object-fit: contain;">
                    </div>
                    <div class="flex-grow-1 ms-4">
                        <h5 class="mb-1 fw-bold">{{ $authority->authority_name }}</h5>
                        <p class="mb-0 text-muted fst-italic">{{ $authority->abbreviation }}</p>
                        <p class="mb-0 text-secondary small">{{ $authority->contact_info }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted">
                Tiada agensi ditemui.
            </div>
        @endforelse
    </div>
</div>

<style>
.hover-shadow:hover {
    box-shadow: 0 0.75rem 1.25rem rgba(0, 0, 0, 0.1);
    transform: translateY(-4px);
    transition: 0.3s ease;
}
</style>
@endsection
