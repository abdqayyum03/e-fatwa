{{-- Shared by preachers.create and preachers.edit. $preacher is null when creating. --}}
@php($preacher = $preacher ?? null)

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
    <div class="col-12">
        <label for="preacher_name" class="form-label">Nama *</label>
        <input type="text" id="preacher_name" name="preacher_name" class="form-control" value="{{ old('preacher_name', $preacher?->preacher_name) }}" required>
    </div>
    <div class="col-md-6">
        <label for="location" class="form-label">Lokasi</label>
        <input type="text" id="location" name="location" class="form-control" placeholder="cth. Shah Alam" value="{{ old('location', $preacher?->location) }}">
    </div>
    <div class="col-md-6">
        <label for="state" class="form-label">Negeri</label>
        <input type="text" id="state" name="state" class="form-control" list="state-list" value="{{ old('state', $preacher?->state) }}">
        <datalist id="state-list">
            @foreach(['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Wilayah Persekutuan'] as $s)
                <option value="{{ $s }}">
            @endforeach
        </datalist>
    </div>
    <div class="col-12">
        <label for="authority_id" class="form-label">Agensi penauliah</label>
        <select id="authority_id" name="authority_id" class="form-select">
            <option value="">-- Tiada --</option>
            @foreach($authorities as $authority)
                <option value="{{ $authority->id }}" @selected(old('authority_id', $preacher?->authority_id) == $authority->id)>{{ $authority->authority_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label for="certification_details" class="form-label">Butiran tauliah</label>
        <textarea id="certification_details" name="certification_details" class="form-control" rows="4">{{ old('certification_details', $preacher?->certification_details) }}</textarea>
    </div>
</div>
