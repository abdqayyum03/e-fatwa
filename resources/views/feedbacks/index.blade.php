@extends('layouts.app')

@section('content')
<div class="p-5 rounded shadow-lg text-white position-relative overflow-hidden" style="
    background: url('{{ asset('images/fatwa-bg.jpg') }}') no-repeat center center;
    background-size: cover;
">
    <div class="dashboard py-5" style="background-color: rgba(248, 249, 250, 0.6);">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Maklum Balas</h2>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>

            <div class="text-center mb-4">
                <p class="text-muted">Kami menghargai sebarang pandangan dan cadangan anda untuk menambah baik sistem e-Fatwa.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white fw-semibold">
                            Hantar Maklum Balas Anda
                        </div>
                        <div class="card-body">
                            <form action="{{ route('feedbacks.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Masukkan nama anda">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Emel</label>
                                    <input type="email" name="email" class="form-control" required placeholder="Contoh: nama@email.com">
                                </div>
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Nombor Telefon</label>
                                    <input type="text" name="phone_number" class="form-control" required placeholder="Contoh: 0123456789">
                                </div>
                                <div class="mb-3">
                                    <label for="content" class="form-label">Maklum Balas</label>
                                    <textarea name="content" rows="5" class="form-control" required placeholder="Tulis maklum balas anda di sini..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-send"></i> Hantar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard {
    background-color: rgba(248, 249, 250, 0.6);
}
</style>
@endsection
