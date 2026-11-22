@extends('layouts.app')

@section('title', 'Maklum Balas')

@section('content')
@include('partials.page-hero', [
    'title' => 'Maklum Balas',
    'subtitle' => 'Kami menghargai pandangan dan cadangan anda untuk menambah baik sistem e-Fatwa.',
    'icon' => 'chat-heart',
    'crumbs' => ['Maklum Balas' => route('feedbacks.index')],
])

<div class="container-xl page-body">
    <div class="row g-4 justify-content-center">
        <div class="col-lg-7">
            <form action="{{ route('feedbacks.store') }}" method="POST" class="surface p-4 p-md-5">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-floating">
                            <input type="text" id="name" name="name" class="form-control" placeholder="Nama" value="{{ old('name') }}" required>
                            <label for="name">Nama</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="email" id="email" name="email" class="form-control" placeholder="E-mel" value="{{ old('email') }}" required>
                            <label for="email">E-mel</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="tel" id="phone_number" name="phone_number" class="form-control" placeholder="Telefon" value="{{ old('phone_number') }}" required>
                            <label for="phone_number">No. telefon</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea id="content" name="content" class="form-control" placeholder="Maklum balas" style="height: 160px" maxlength="2000" required>{{ old('content') }}</textarea>
                            <label for="content">Maklum balas anda</label>
                        </div>
                        <div class="form-text text-end"><span id="charCount">0</span> / 2000</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-3"><i class="bi bi-send"></i> Hantar Maklum Balas</button>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="surface p-4 mb-3">
                <h2 class="h5">Apa yang boleh anda kongsi?</h2>
                <ul class="info-list mt-2">
                    <li><i class="bi bi-lightbulb"></i><div><small>Cadangan</small>Ciri baharu atau negeri yang patut ditambah.</div></li>
                    <li><i class="bi bi-bug"></i><div><small>Masalah</small>Pautan rosak, teks fatwa yang salah atau halaman yang tidak berfungsi.</div></li>
                    <li><i class="bi bi-question-circle"></i><div><small>Pertanyaan</small>Soalan tentang penggunaan sistem ini.</div></li>
                </ul>
            </div>
            <div class="surface p-4 small text-muted">
                <i class="bi bi-shield-check text-primary"></i>
                Maklumat anda hanya digunakan untuk membalas maklum balas ini. Untuk pertanyaan hukum, sila hubungi jabatan mufti negeri anda secara terus.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function () {
            const t = document.getElementById('content'), c = document.getElementById('charCount');
            const update = () => { c.textContent = t.value.length; };
            t.addEventListener('input', update); update();
        })();
    </script>
@endpush
