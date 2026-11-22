{{-- Shared by lectures.create and lectures.edit. $lecture is null when creating. --}}
@php
    $lecture = $lecture ?? null;
    $prefill = $prefill ?? [];
@endphp

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <label for="title" class="form-label">Tajuk *</label>
        <input type="text" id="title" name="title" class="form-control" placeholder="cth. Kuliah Maghrib: Tafsir Surah Yasin" value="{{ old('title', $lecture?->title) }}" required>
    </div>
    <div class="col-md-4">
        <label for="type" class="form-label">Jenis *</label>
        <select id="type" name="type" class="form-select" required>
            @foreach(\App\Models\Lecture::TYPES as $type)
                <option value="{{ $type }}" @selected(old('type', $lecture?->type ?? 'Kuliah') === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="starts_at" class="form-label">Mula *</label>
        <input type="datetime-local" id="starts_at" name="starts_at" class="form-control" value="{{ old('starts_at', $lecture?->starts_at?->format('Y-m-d\TH:i')) }}" required>
    </div>
    <div class="col-md-6">
        <label for="ends_at" class="form-label">Tamat</label>
        <input type="datetime-local" id="ends_at" name="ends_at" class="form-control" value="{{ old('ends_at', $lecture?->ends_at?->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-md-6">
        <label for="mosque_id" class="form-label">Masjid</label>
        <select id="mosque_id" name="mosque_id" class="form-select">
            <option value="">-- Tempat lain --</option>
            @foreach($mosques as $mosque)
                <option value="{{ $mosque->id }}" @selected(old('mosque_id', $lecture?->mosque_id ?? ($prefill['mosque_id'] ?? null)) == $mosque->id)>{{ $mosque->name }} ({{ $mosque->location }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label for="venue" class="form-label">Tempat lain</label>
        <input type="text" id="venue" name="venue" class="form-control" placeholder="Jika bukan di masjid dalam senarai" value="{{ old('venue', $lecture?->venue) }}">
    </div>

    <div class="col-12">
        <label for="preacher_id" class="form-label">Penceramah</label>
        <select id="preacher_id" name="preacher_id" class="form-select">
            <option value="">-- Tiada / tidak dinyatakan --</option>
            @foreach($preachers as $preacher)
                <option value="{{ $preacher->id }}" @selected(old('preacher_id', $lecture?->preacher_id ?? ($prefill['preacher_id'] ?? null)) == $preacher->id)>{{ $preacher->preacher_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Penerangan</label>
        <textarea id="description" name="description" class="form-control" rows="5" placeholder="Topik, kitab yang dibaca, nota untuk hadirin...">{{ old('description', $lecture?->description) }}</textarea>
    </div>
</div>
