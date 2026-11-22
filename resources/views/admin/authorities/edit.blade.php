@extends('admin.layout', [
    'heading' => __('Edit agensi'),
    'subheading' => $authority->authority_name,
    'icon' => 'bank',
])

@section('title', __('Edit agensi'))

@section('admin')
<form method="POST" action="{{ route('admin.authorities.update', $authority) }}" enctype="multipart/form-data" class="surface p-4">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-md-8">
            <label for="authority_name" class="form-label">{{ __('Nama agensi *') }}</label>
            <input id="authority_name" name="authority_name" class="form-control" value="{{ old('authority_name', $authority->authority_name) }}" required maxlength="255">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="state">{{ __('Negeri') }}</label>
            <input id="state" class="form-control" value="{{ $authority->state }}" disabled>
            <div class="form-text">{{ __('Negeri ialah kunci yang digunakan oleh pengimport, jadi tidak boleh ditukar.') }}</div>
        </div>

        <div class="col-md-8">
            <label for="street" class="form-label">{{ __('Alamat') }}</label>
            <input id="street" name="street" class="form-control" value="{{ old('street', $authority->street) }}" maxlength="255">
        </div>
        <div class="col-md-4">
            <label for="poscode" class="form-label">{{ __('Poskod') }}</label>
            <input id="poscode" name="poscode" class="form-control" value="{{ old('poscode', $authority->poscode) }}" maxlength="10">
        </div>
        <div class="col-md-6">
            <label for="city" class="form-label">{{ __('Bandar') }}</label>
            <input id="city" name="city" class="form-control" value="{{ old('city', $authority->city) }}" maxlength="100">
        </div>
        <div class="col-md-6">
            <label for="contact_info" class="form-label">{{ __('No. telefon') }}</label>
            <input id="contact_info" name="contact_info" class="form-control" value="{{ old('contact_info', $authority->contact_info) }}" maxlength="255">
        </div>
        <div class="col-12">
            <label for="website" class="form-label">{{ __('Laman web') }}</label>
            <input id="website" name="website" type="url" class="form-control" value="{{ old('website', $authority->website) }}" placeholder="https://" maxlength="255">
        </div>

        <div class="col-12">
            <label for="logo" class="form-label">{{ __('Logo') }}</label>
            <div class="d-flex align-items-center gap-3">
                @if($authority->logo)
                    <img src="{{ asset('images/authorities/'.$authority->logo) }}" alt="{{ __('Logo semasa') }}" style="height: 64px; width: 64px; object-fit: contain" class="border rounded p-1">
                @endif
                <div class="flex-grow-1">
                    <input id="logo" name="logo" type="file" class="form-control" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">{{ __('PNG, JPG atau WebP, maksimum 1 MB.') }}</div>
                    @if($authority->logo)
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                            <label class="form-check-label small" for="remove_logo">{{ __('Buang logo semasa') }}</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary"><i class="bi bi-save"></i> {{ __('Simpan') }}</button>
        <a href="{{ route('admin.authorities.index') }}" class="btn btn-outline-secondary">{{ __('Batal') }}</a>
    </div>
</form>
@endsection
