@extends('layouts.app')

@section('content')
<div class="container-fluid py-5 px-5">
    <a href="{{ url('/') }}" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left-circle"></i> Kembali ke Dashboard
    </a>

    <h2 class="mb-4 text-center fw-bold">Senarai Fatwa Terkini</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search and Filter Form -->
    <form method="GET" action="{{ route('fatwas.index') }}" class="row mb-4 g-2 align-items-center">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari berdasarkan tajuk atau kandungan...">
        </div>
        <div class="col-md-3">
            <select name="year" class="form-select" onchange="this.form.submit()">
                <option value="">-- Pilih Tahun --</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="agency" class="form-select" onchange="this.form.submit()">
                <option value="">-- Pilih Agensi --</option>
                @foreach($agencies as $agency)
                    <option value="{{ $agency->id }}" {{ request('agency') == $agency->id ? 'selected' : '' }}>
                        {{ $agency->authority_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Cari
            </button>
        </div>
    </form>

    <!-- Table Section -->
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Tajuk Fatwa</th>
                    <th scope="col">Tahun Diwartakan</th>
                    <th scope="col">Agensi</th>
                    <th scope="col">Butiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fatwas as $index => $fatwa)
                    <tr>
                        <td>{{ $fatwas->firstItem() + $index }}</td>
                        <td>{{ $fatwa->title }}</td>
                        <td>{{ $fatwa->date_issued }}</td>
                        <td>{{ $fatwa->authority->authority_name ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('fatwas.show', $fatwa->id) }}" class="btn btn-sm btn-info text-white">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Tiada fatwa dijumpai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $fatwas->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
