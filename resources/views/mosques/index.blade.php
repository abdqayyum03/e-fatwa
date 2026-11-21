@extends('layouts.app')

@section('content')
<div class="p-5 rounded shadow-lg text-white position-relative overflow-hidden" style="
    background: url('{{ asset('images/fatwa-bg.jpg') }}') no-repeat center center;
    background-size: cover;
">
<div class="dashboard py-5" style="background-color: rgba(248, 249, 250, 0.85);">
    <div class="container">

        <!-- Return to Dashboard -->
        <div class="mb-4 text-end">
            <a href="{{ url('/') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <h2 class="mb-4 text-center fw-bold">Senarai Masjid di Malaysia</h2>

        <!-- Filter Section -->
        <form method="GET" action="{{ route('mosques.index') }}" class="row mb-4 g-2 justify-content-center">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama atau alamat masjid...">
            </div>
            <div class="col-md-3">
                <select name="location" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Tapis Mengikut Negeri --</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>

        <!-- Mosque Cards -->
        <div class="row g-4">
            @forelse($mosques as $mosque)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm hover-shadow transition">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">{{ $mosque->name }}</h5>
                            <p class="card-text text-muted mb-1"><strong>Lokasi:</strong> {{ $mosque->location }}</p>
                            <p class="card-text text-muted mb-1"><strong>Alamat:</strong> {{ $mosque->address }}</p>
                            <p class="card-text text-muted"><strong>Telefon:</strong> {{ $mosque->contact_info }}</p>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($mosque->address) }}" target="_blank" class="btn btn-outline-primary w-100">
                                Lihat di Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">
                    Tiada masjid dijumpai.
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $mosques->withQueryString()->links() }}
        </div>
    </div>
</div>
</div>

<style>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-3px);
    transition: 0.3s ease;
}
</style>
@endsection
