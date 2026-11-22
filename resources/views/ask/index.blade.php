@extends('layouts.app')

@section('title', __('Tanya AI'))

@section('content')
@include('partials.page-hero', [
    'title' => __('Tanya AI'),
    'subtitle' => __('Tanya soalan dan dapatkan ringkasan daripada fatwa sebenar dalam pangkalan data, lengkap dengan rujukan.'),
    'icon' => 'stars',
    'crumbs' => [__('Tanya AI') => route('ask.index')],
])

<div class="container-xl page-body">
    <div class="row g-4">
        <div class="col-lg-8">
            @unless($enabled)
                <div class="surface empty">
                    <i class="bi bi-plug"></i>
                    <p class="mb-1">{{ __('Pembantu AI belum dikonfigurasi.') }}</p>
                    <p class="small mb-0">{{ __('Pentadbir perlu menetapkan ANTHROPIC_API_KEY dalam fail .env.') }}</p>
                </div>
            @elseguest
                <div class="surface empty">
                    <i class="bi bi-person-lock"></i>
                    <p class="mb-3">{{ __('Log masuk atau daftar akaun percuma untuk bertanya kepada pembantu AI.') }}</p>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">{{ __('Log masuk') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm">{{ __('Daftar') }}</a>
                </div>
            @else
                <form method="POST" action="{{ route('ask.store') }}" class="surface p-4 mb-4" data-ask>
                    @csrf
                    <label for="question" class="form-label">{{ __('Soalan anda') }}</label>
                    <textarea id="question" name="question" class="form-control mb-2" rows="3" maxlength="500" minlength="5" required
                              placeholder="{{ __('cth. Apakah hukum menggunakan vape?') }}">{{ old('question', $question ?? '') }}</textarea>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach([__('Apakah hukum rokok elektronik dan vape?'), __('Bagaimana zakat pendapatan dikira?'), __('Adakah ajaran Syiah dibenarkan di Malaysia?')] as $example)
                                <button type="button" class="chip chip-light py-1 border-0" data-ask-example>{{ $example }}</button>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-stars"></i> {{ __('Tanya') }}</button>
                    </div>
                    @error('question')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </form>

                <div data-ask-result>
                    @isset($result)
                        @include('ask._answer', ['result' => $result])
                    @endisset
                </div>
            @endunless
        </div>

        <div class="col-lg-4">
            <div class="surface p-4 sticky-side">
                <h2 class="h6 fw-bold"><i class="bi bi-shield-check text-primary"></i> {{ __('Cara ia berfungsi') }}</h2>
                <ol class="small text-muted ps-3 mb-3">
                    <li>{{ __('Sistem mencari fatwa yang paling berkaitan dalam pangkalan data e-Fatwa.') }}</li>
                    <li>{{ __('AI meringkaskan apa yang dinyatakan oleh fatwa tersebut sahaja, dengan rujukan bernombor.') }}</li>
                    <li>{{ __('Klik nombor rujukan untuk membaca fatwa asal.') }}</li>
                </ol>
                <div class="p-3 rounded-3 small" style="background: var(--ef-gold-soft)">
                    <strong>{{ __('Penafian:') }}</strong>
                    {{ __('Jawapan ini ialah ringkasan automatik, bukan fatwa atau keputusan hukum. AI boleh tersilap. Sentiasa rujuk dokumen fatwa rasmi dan jabatan mufti negeri anda.') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
