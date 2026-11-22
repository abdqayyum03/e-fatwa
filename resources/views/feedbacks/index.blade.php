@extends('layouts.app')

@section('title', __('Maklum Balas'))

@section('content')
@include('partials.page-hero', [
    'title' => __('Maklum Balas'),
    'subtitle' => __('Kami menghargai pandangan dan cadangan anda untuk menambah baik sistem e-Fatwa.'),
    'icon' => 'chat-heart',
    'crumbs' => [__('Maklum Balas') => route('feedbacks.index')],
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
                            <input type="text" id="name" name="name" class="form-control" placeholder="{{ __('Nama') }}" value="{{ old('name') }}" required>
                            <label for="name">{{ __('Nama') }}</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="email" id="email" name="email" class="form-control" placeholder="{{ __('E-mel') }}" value="{{ old('email') }}" required>
                            <label for="email">{{ __('E-mel') }}</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="tel" id="phone_number" name="phone_number" class="form-control" placeholder="{{ __('Telefon') }}" value="{{ old('phone_number') }}" required>
                            <label for="phone_number">{{ __('No. telefon') }}</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea id="content" name="content" class="form-control" placeholder="{{ __('Maklum balas') }}" style="height: 160px" maxlength="2000" required>{{ old('content') }}</textarea>
                            <label for="content">{{ __('Maklum balas anda') }}</label>
                        </div>
                        <div class="form-text text-end"><span id="charCount">0</span> / 2000</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-3"><i class="bi bi-send"></i> {{ __('Hantar Maklum Balas') }}</button>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="surface p-4 mb-3">
                <h2 class="h5">{{ __('Apa yang boleh anda kongsi?') }}</h2>
                <ul class="info-list mt-2">
                    <li><i class="bi bi-lightbulb"></i><div><small>{{ __('Cadangan') }}</small>{{ __('Ciri baharu atau negeri yang patut ditambah.') }}</div></li>
                    <li><i class="bi bi-bug"></i><div><small>{{ __('Masalah') }}</small>{{ __('Pautan rosak, teks fatwa yang salah atau halaman yang tidak berfungsi.') }}</div></li>
                    <li><i class="bi bi-question-circle"></i><div><small>{{ __('Pertanyaan') }}</small>{{ __('Soalan tentang penggunaan sistem ini.') }}</div></li>
                </ul>
            </div>
            <div class="surface p-4 small text-muted">
                <i class="bi bi-shield-check text-primary"></i>
                {{ __('Maklumat anda hanya digunakan untuk membalas maklum balas ini. Untuk pertanyaan hukum, sila hubungi jabatan mufti negeri anda secara terus.') }}
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
