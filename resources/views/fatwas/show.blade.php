@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Card Style -->
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h3 class="mb-0">{{ $fatwa->title }}</h3>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Tahun Diwartakan:</h6>
                        <p class="fw-semibold">{{ \Carbon\Carbon::parse($fatwa->date_issued)->format('Y') }}</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Agensi Penguatkuasa:</h6>
                        <p class="fw-semibold">{{ $fatwa->authority->authority_name ?? 'N/A' }}</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Kandungan Fatwa:</h6>

                        @if($fatwa->content)
                            <a href="{{ $fatwa->content }}" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-file-earmark-text"></i> Buka Kandungan Fatwa
                            </a>
                        @else
                            <span class="text-muted">Tiada pautan kandungan disediakan.</span>
                        @endif
                    </div>

                    @if($fatwa->link)
                        <div class="text-center mt-3">
                            <a href="{{ $fatwa->link }}" target="_blank" class="btn btn-info text-white">
                                <i class="bi bi-box-arrow-up-right"></i> Lihat Fail Fatwa Tambahan
                            </a>
                        </div>
                    @endif
                </div>

                <div class="card-footer text-center bg-light rounded-bottom-4">
                    <a href="{{ route('fatwas.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Kembali ke Senarai Fatwa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
