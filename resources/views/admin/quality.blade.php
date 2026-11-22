@extends('admin.layout', [
    'heading' => __('Kualiti data'),
    'subheading' => __('Fatwa yang perlu disemak. Betulkan terus di sini, atau tandakan sebagai sudah disemak untuk membuangnya daripada senarai.'),
    'icon' => 'clipboard-check',
])

@section('title', __('Kualiti data'))

@php
    $hints = [
        'no_text' => __('Fatwa ini tiada teks (imbasan yang tidak dapat dibaca, atau tiada dokumen). Jalankan semula import untuk mencuba OCR, atau tanda sebagai disemak jika tiada apa yang boleh dibuat.'),
        'no_category' => __('Pilih kategori dan simpan. Untuk banyak fatwa sekaligus, gunakan peraturan kata kunci di halaman Kategori.'),
        'no_date' => __('Masukkan tarikh daripada dokumen rasmi, cth. 12 Mac 2024, atau tahun sahaja.'),
        'no_decision' => __('Teks ada tetapi tiada ayat keputusan yang dikenal pasti, jadi kotak Keputusan tidak dipaparkan. Biasanya kerana dokumen tidak menggunakan perkataan lazim seperti "memutuskan".'),
        'garbled' => __('Teks OCR yang kebanyakannya bukan perkataan (cop, tulisan Jawi, imbasan kabur). Semak PDF rasmi.'),
        'duplicates' => __('Fatwa daripada agensi yang sama dengan tajuk yang sama. Kadangkala memang dua fatwa berbeza (contohnya pindaan); jika benar-benar pendua, padam salah satu.'),
    ];
@endphp

@section('admin')
{{-- Issue picker --}}
<div class="surface p-3 mb-3">
    <div class="issue-grid">
        @foreach(\App\Services\FatwaQuality::ISSUES as $key => [$label, $icon])
            <a class="issue-tile @if($issue === $key) active @endif @if(! $counts[$key]) is-clear @endif"
               href="{{ route('admin.quality.index', array_filter(['isu' => $key, 'negeri' => $state])) }}"
               @if($issue === $key) aria-current="page" @endif>
                <i class="bi bi-{{ $icon }}"></i>
                <span class="issue-count">{{ number_format($counts[$key]) }}</span>
                <span class="issue-label">{{ __($label) }}</span>
            </a>
        @endforeach
    </div>
</div>

{{-- Toolbar --}}
<div class="quality-toolbar mb-3">
    <p class="small text-muted mb-0"><i class="bi bi-info-circle"></i> {{ $hints[$issue] }}</p>
    <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
        <form method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="isu" value="{{ $issue }}">
            <label for="negeri" class="visually-hidden">{{ __('Negeri') }}</label>
            <select id="negeri" name="negeri" class="form-select form-select-sm" style="min-width: 11rem" onchange="this.form.submit()">
                <option value="">{{ __('Semua negeri') }}</option>
                @foreach($states as $s)
                    <option value="{{ $s }}" @selected($state === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <noscript><button class="btn btn-sm btn-outline-primary">{{ __('Tapis') }}</button></noscript>
        </form>
        <span class="result-count text-nowrap"><strong class="text-body">{{ number_format($fatwas->total()) }}</strong> {{ __('fatwa') }}</span>
    </div>
</div>

<div class="surface overflow-hidden">
    @forelse($fatwas as $fatwa)
        <div class="quality-row">
            <div class="quality-main">
                <a href="{{ route('fatwas.show', $fatwa) }}" target="_blank" class="quality-title">{{ $fatwa->title }}</a>
                <div class="meta-row">
                    <x-state-pill :state="$fatwa->authority->state ?? null" />
                    <span class="tag">#{{ $fatwa->id }}</span>
                    @if($issue !== 'no_date')<span class="tag"><i class="bi bi-calendar3"></i> {{ $fatwa->date_issued ?: __('Tiada tarikh') }}</span>@endif
                    @if($issue !== 'no_category')<span class="tag"><i class="bi bi-tag"></i> {{ $fatwa->category->category_name ?? __('Tiada kategori') }}</span>@endif
                    @if($fatwa->text_from_ocr)<span class="tag">OCR</span>@endif
                    @if($fatwa->pdf_url)<a class="tag tag-link" href="{{ $fatwa->pdf_url }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i> PDF</a>@endif
                    @if($fatwa->source_url)<a class="tag tag-link" href="{{ $fatwa->source_url }}" target="_blank" rel="noopener"><i class="bi bi-link-45deg"></i> {{ __('Sumber') }}</a>@endif
                </div>
                @if(in_array($issue, ['garbled', 'no_decision']) && $fatwa->full_text)
                    <div class="quality-snippet mt-2">{{ \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', $fatwa->full_text), 260) }}</div>
                @endif
            </div>

            <div class="quality-actions">
                {{-- The one fix this list is about, right in the row --}}
                @if($issue === 'no_category')
                    <form method="POST" action="{{ route('admin.quality.update', $fatwa) }}" class="input-group input-group-sm quick-fix">
                        @csrf @method('PATCH')
                        <select name="category_id" class="form-select" aria-label="{{ __('Kategori untuk :title', ['title' => $fatwa->title]) }}" required>
                            <option value="">{{ __('Pilih kategori') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary">{{ __('Simpan') }}</button>
                    </form>
                @elseif($issue === 'no_date')
                    <form method="POST" action="{{ route('admin.quality.update', $fatwa) }}" class="input-group input-group-sm quick-fix">
                        @csrf @method('PATCH')
                        <input name="date_issued" class="form-control" placeholder="{{ __('cth. 12 Mac 2024') }}" aria-label="{{ __('Tarikh untuk :title', ['title' => $fatwa->title]) }}" required maxlength="100">
                        <button class="btn btn-primary">{{ __('Simpan') }}</button>
                    </form>
                @endif

                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#fix-{{ $fatwa->id }}"
                            aria-expanded="false" aria-controls="fix-{{ $fatwa->id }}" title="{{ __('Betulkan tajuk, kategori atau tarikh') }}">
                        <i class="bi bi-pencil"></i> {{ __('Betulkan') }}
                    </button>
                    <form method="POST" action="{{ route('admin.quality.update', $fatwa) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="checked" value="1">
                        <button class="btn btn-sm btn-outline-success" title="{{ __('Tiada apa perlu dibuat; buang daripada senarai') }}">
                            <i class="bi bi-eye-slash"></i> {{ __('Disemak') }}
                        </button>
                    </form>
                    @if($issue === 'duplicates')
                        <form method="POST" action="{{ route('fatwas.destroy', $fatwa) }}" data-confirm="{{ __('Padam fatwa #:id? Tindakan ini tidak boleh dibatalkan.', ['id' => $fatwa->id]) }}">
                            @csrf @method('DELETE')
                            <input type="hidden" name="back" value="1">
                            <button class="btn btn-sm btn-outline-danger" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}"><i class="bi bi-trash"></i></button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Full fix form, folded away --}}
            <div class="collapse quality-fix" id="fix-{{ $fatwa->id }}">
                <form method="POST" action="{{ route('admin.quality.update', $fatwa) }}" class="row g-2 align-items-end">
                    @csrf @method('PATCH')
                    <div class="col-12">
                        <label class="form-label small mb-1" for="title-{{ $fatwa->id }}">{{ __('Tajuk') }}</label>
                        <input id="title-{{ $fatwa->id }}" name="title" class="form-control form-control-sm" value="{{ $fatwa->title }}" required maxlength="500">
                    </div>
                    <div class="col-sm-6 col-lg-5">
                        <label class="form-label small mb-1" for="cat-{{ $fatwa->id }}">{{ __('Kategori') }}</label>
                        <select id="cat-{{ $fatwa->id }}" name="category_id" class="form-select form-select-sm">
                            <option value="">{{ __('Tiada kategori') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected($fatwa->category_id === $category->id)>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <label class="form-label small mb-1" for="date-{{ $fatwa->id }}">{{ __('Tarikh') }}</label>
                        <input id="date-{{ $fatwa->id }}" name="date_issued" class="form-control form-control-sm" value="{{ $fatwa->date_issued }}" placeholder="{{ __('cth. 12 Mac 2024 atau 2024') }}">
                    </div>
                    <div class="col-lg-3 d-flex gap-2">
                        <button class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-save"></i> {{ __('Simpan') }}</button>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('fatwas.edit', $fatwa) }}" title="{{ __('Edit penuh') }}" aria-label="{{ __('Edit penuh') }}"><i class="bi bi-box-arrow-up-right"></i></a>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="empty"><i class="bi bi-emoji-smile"></i>{{ __('Tiada fatwa dalam senarai ini.') }}</div>
    @endforelse
</div>

<div class="mt-3">{{ $fatwas->links() }}</div>
@endsection
