@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-center fw-bold">Maklumat Jabatan Mufti</h2>

    <div class="card shadow-sm mb-4">
        <div class="row g-0 align-items-center">
            <div class="col-md-3 text-center p-3">
                @if($authority->logo)
                    <img src="{{ asset('images/authorities/' . $authority->logo) }}" alt="Logo {{ $authority->authority_name }}" class="img-fluid rounded" style="max-height: 150px;">
                @else
                    <p class="text-muted">Tiada logo</p>
                @endif
            </div>
            <div class="col-md-9">
                <div class="card-body">
                    <h3 class="card-title fw-bold">{{ $authority->authority_name }}</h3>
                    <p class="text-muted mb-1"><strong>Alamat:</strong> {{ $authority->street }}, {{ $authority->city }}, {{ $authority->state }}, {{ $authority->poscode }}</p>
                    <p class="text-muted"><strong>Hubungi:</strong> {{ $authority->contact_info }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="fw-bold">Sejarah</h4>
            <p class="text-justify">{!! nl2br(e($authority->sejarah)) !!}</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="fw-bold">Visi</h4>
            <p class="text-justify">{!! nl2br(e($authority->visi)) !!}</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="fw-bold">Misi</h4>
            <p class="text-justify">{!! nl2br(e($authority->misi)) !!}</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="fw-bold">Objektif</h4>
            <p class="text-justify">{!! nl2br(e($authority->objektif)) !!}</p>
        </div>
    </div>

    <!-- Organization Chart -->
    @if($authority->org_chart)
        <div class="mb-5 text-center">
            <h4 class="fw-bold text-primary mb-3">Carta Organisasi</h4>
            <img src="{{ asset('images/authorities/' . $authority->org_chart) }}" alt="Carta Organisasi {{ $authority->authority_name }}" class="img-fluid rounded shadow-sm">
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('authorities.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle"></i> Kembali ke Senarai Agensi
        </a>
    </div>
</div>
@endsection
