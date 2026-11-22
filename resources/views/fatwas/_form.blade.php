{{-- Shared by fatwas.create and fatwas.edit. $fatwa is null when creating. --}}
@php($fatwa = $fatwa ?? null)

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label for="title" class="form-label">Tajuk Fatwa *</label>
    <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $fatwa?->title) }}" required>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="issuing_agency" class="form-label">Agensi *</label>
        <select id="issuing_agency" name="issuing_agency" class="form-select" required>
            <option value="">-- Pilih Agensi --</option>
            @foreach($authorities as $authority)
                <option value="{{ $authority->id }}" @selected(old('issuing_agency', $fatwa?->issuing_agency) == $authority->id)>
                    {{ $authority->authority_name }} ({{ $authority->state }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="category_id" class="form-label">Kategori</label>
        <select id="category_id" name="category_id" class="form-select">
            <option value="">-- Tiada --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $fatwa?->category_id) == $category->id)>
                    {{ $category->category_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="date_issued" class="form-label">Tarikh / Tahun Diwartakan *</label>
        <input type="text" id="date_issued" name="date_issued" class="form-control" placeholder="cth. 10 Mac 2025 atau 2024"
               value="{{ old('date_issued', $fatwa?->date_issued) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="gazette_no" class="form-label">No. Warta</label>
        <input type="text" id="gazette_no" name="gazette_no" class="form-control" placeholder="cth. P.U. (B) 59"
               value="{{ old('gazette_no', $fatwa?->gazette_no) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-select">
            <option value="">-- Tidak diketahui --</option>
            @foreach(['Diwartakan', 'Tidak Diwartakan'] as $status)
                <option value="{{ $status }}" @selected(old('status', $fatwa?->status) === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="pdf_url" class="form-label">Pautan PDF</label>
        <input type="url" id="pdf_url" name="pdf_url" class="form-control" value="{{ old('pdf_url', $fatwa?->pdf_url) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="source_url" class="form-label">Pautan Sumber Asal</label>
        <input type="url" id="source_url" name="source_url" class="form-control" value="{{ old('source_url', $fatwa?->source_url) }}">
    </div>
</div>

<div class="mb-3">
    <label for="content" class="form-label">Ringkasan</label>
    <textarea id="content" name="content" class="form-control" rows="3">{{ old('content', $fatwa?->content) }}</textarea>
</div>

<div class="mb-3">
    <label for="full_text" class="form-label">Teks Penuh Fatwa</label>
    <textarea id="full_text" name="full_text" class="form-control" rows="10">{{ old('full_text', $fatwa?->full_text) }}</textarea>
    <div class="form-text">Diisi secara automatik oleh pengikis (scraper) jika PDF mempunyai teks.</div>
</div>
