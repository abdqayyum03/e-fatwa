@extends('layouts.app')

@section('title', __('Akaun Saya'))

@section('content')
@include('partials.page-hero', [
    'title' => __('Akaun Saya'),
    'subtitle' => __('Urus profil, makluman e-mel dan kata laluan anda.'),
    'icon' => 'person-gear',
    'crumbs' => [__('Akaun') => route('account.edit')],
])

<div class="container-xl page-body">
    <div class="row g-4">
        <div class="col-lg-3">@include('account._nav')</div>

        <div class="col-lg-9 d-grid gap-4">
            @if ($errors->any())
                <div class="alert alert-danger mb-0"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('account.update') }}" class="surface p-4 p-md-5">
                @csrf
                @method('PUT')

                <h2 class="h5 mb-3">{{ __('Profil') }}</h2>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('Nama') }}</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('E-mel') }}</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    </div>
                </div>

                <h2 class="h5 mb-1">{{ __('Makluman fatwa baharu') }}</h2>
                <p class="text-muted small">{{ __('Terima e-mel mingguan apabila fatwa baharu ditambah dalam kategori atau negeri yang anda ikuti.') }}</p>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="alerts_enabled" name="alerts_enabled" value="1" @checked(old('alerts_enabled', $user->alerts_enabled))>
                    <label class="form-check-label fw-semibold" for="alerts_enabled">{{ __('Hantar makluman mingguan kepada saya') }}</label>
                </div>

                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="small fw-bold text-uppercase text-muted mb-2">{{ __('Kategori') }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($categories as $category)
                                <input type="checkbox" class="btn-check" name="alert_categories[]" id="cat{{ $category->id }}" value="{{ $category->id }}"
                                       @checked(in_array($category->id, old('alert_categories', $user->alert_categories ?? [])))>
                                <label class="btn btn-outline-primary btn-sm" for="cat{{ $category->id }}">{{ $category->category_name }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="small fw-bold text-uppercase text-muted mb-2">{{ __('Negeri') }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($states as $state)
                                <input type="checkbox" class="btn-check" name="alert_states[]" id="st{{ crc32($state) }}" value="{{ $state }}"
                                       @checked(in_array($state, old('alert_states', $user->alert_states ?? [])))>
                                <label class="btn btn-outline-primary btn-sm" for="st{{ crc32($state) }}">{{ $state }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <p class="form-text mt-3 mb-0">
                    <i class="bi bi-info-circle"></i>
                    {{ __('Jika tiada kategori atau negeri dipilih, anda akan menerima semua fatwa baharu.') }}
                    @if($user->last_alert_sent_at)
                        {{ __('Makluman terakhir dihantar :time.', ['time' => $user->last_alert_sent_at->diffForHumans()]) }}
                    @endif
                </p>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> {{ __('Simpan tetapan') }}</button>
                </div>
            </form>

            <form method="POST" action="{{ route('account.password') }}" class="surface p-4 p-md-5">
                @csrf
                @method('PUT')
                <h2 class="h5 mb-3">{{ __('Tukar kata laluan') }}</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="current_password" class="form-label">{{ __('Kata laluan semasa') }}</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="col-md-4">
                        <label for="password" class="form-label">{{ __('Kata laluan baharu') }}</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="col-md-4">
                        <label for="password_confirmation" class="form-label">{{ __('Sahkan kata laluan') }}</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-outline-primary">{{ __('Kemas kini kata laluan') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
