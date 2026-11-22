@extends('layouts.app')

@section('title', \Illuminate\Support\Str::limit($fatwa->title, 60))

@section('content')
@include('partials.page-hero', [
    'title' => $fatwa->title,
    'icon' => 'journal-bookmark',
    'crumbs' => [__('Fatwa') => route('fatwas.index'), $fatwa->title => null],
])

<div class="container-xl page-body">
    <div class="row g-4">
        {{-- Main reading column --}}
        <div class="col-lg-8 order-2 order-lg-1">
            <div class="surface p-3 p-md-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="meta-row mt-0">
                        <x-state-pill :state="$fatwa->authority->state ?? null" />
                        @if($fatwa->status)
                            <span class="tag {{ $fatwa->status === 'Diwartakan' ? 'tag-gold' : '' }}"><i class="bi bi-patch-check"></i> {{ __($fatwa->status) }}</span>
                        @endif
                        @if($fatwa->category)<span class="tag"><i class="bi bi-tag"></i> {{ $fatwa->category->category_name }}</span>@endif
                    </div>
                    @if($fatwa->full_text)
                        <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('Alat bacaan') }}">
                            <button type="button" class="btn btn-outline-primary" data-font="down" title="{{ __('Kecilkan teks') }}"><i class="bi bi-type"></i>−</button>
                            <button type="button" class="btn btn-outline-primary" data-font="up" title="{{ __('Besarkan teks') }}"><i class="bi bi-type"></i>+</button>
                            <button type="button" class="btn btn-outline-primary" data-font="serif" title="{{ __('Tukar fon') }}"><i class="bi bi-fonts"></i></button>
                        </div>
                    @endif
                </div>

                @if($fatwa->content && ! filter_var($fatwa->content, FILTER_VALIDATE_URL))
                    <div class="p-3 rounded-3 mb-3" style="background: var(--ef-gold-soft)">
                        <div class="small fw-bold text-uppercase text-muted mb-1">{{ __('Ringkasan') }}</div>
                        {{ $fatwa->content }}
                    </div>
                @endif

                @if($fatwa->decision)
                    <section class="fatwa-decision mb-4" aria-labelledby="decision-heading">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-check2-circle"></i>
                            <h2 id="decision-heading" class="h6 fw-bold text-uppercase mb-0">{{ __('Keputusan') }}</h2>
                        </div>
                        <div class="fatwa-decision-text">{{ $fatwa->decision }}</div>
                        <p class="small text-muted mt-2 mb-0">{{ __('Dipetik secara automatik daripada teks fatwa. Baca teks penuh di bawah atau dokumen rasmi untuk konteks lengkap.') }}</p>
                    </section>
                @endif

                @if($fatwa->full_text)
                    @if($fatwa->decision)
                        <h2 class="h6 fw-bold text-uppercase text-muted mb-2">{{ __('Teks penuh') }}</h2>
                    @endif
                    <article class="fatwa-doc" data-reading>{{ $fatwa->full_text }}</article>
                    <p class="small text-muted mt-3 mb-0">
                        <i class="bi bi-info-circle"></i>
                        @if($fatwa->text_from_ocr)
                            {{ __('Teks ini dibaca daripada dokumen imbasan menggunakan OCR dan mungkin mengandungi kesilapan ejaan. Rujuk dokumen rasmi untuk kandungan yang sah.') }}
                        @else
                            {{ __('Teks diekstrak secara automatik daripada PDF rasmi dan mungkin mengandungi kesilapan format. Rujuk PDF rasmi untuk kandungan yang sah.') }}
                        @endif
                    </p>
                @else
                    <div class="empty">
                        <i class="bi bi-file-earmark-image"></i>
                        <p class="mb-3">
                            @if($fatwa->pdf_url)
                                Dokumen fatwa ini ialah imbasan, jadi teksnya tidak dapat dipaparkan di sini.
                            @else
                                Teks fatwa ini tidak tersedia dalam pangkalan data.
                            @endif
                        </p>
                        @if($fatwa->pdf_url)
                            <a href="{{ $fatwa->pdf_url }}" target="_blank" rel="noopener" class="btn btn-primary"><i class="bi bi-file-earmark-pdf"></i> {{ __('Buka PDF Rasmi') }}</a>
                        @elseif($fatwa->source_url)
                            <a href="{{ $fatwa->source_url }}" target="_blank" rel="noopener" class="btn btn-primary"><i class="bi bi-box-arrow-up-right"></i> {{ __('Lihat di laman sumber') }}</a>
                        @endif
                    </div>
                @endif
            </div>

            @if($related->isNotEmpty())
                <h2 class="h4 mb-3">Fatwa lain dalam kategori {{ $fatwa->category->category_name }}</h2>
                <div class="surface overflow-hidden">
                    @foreach($related as $other)
                        <a href="{{ route('fatwas.show', $other) }}" class="fatwa-item">
                            <div class="year"><b>{{ $other->issued_year ?? '–' }}</b><small>{{ __('Tahun') }}</small></div>
                            <div class="flex-grow-1">
                                <div class="title">{{ $other->title }}</div>
                                <div class="meta-row"><x-state-pill :state="$other->authority->state ?? null" /></div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Side panel --}}
        <div class="col-lg-4 order-1 order-lg-2">
            <div class="sticky-side d-grid gap-3">
                <div class="surface p-4">
                    <ul class="info-list">
                        <li><i class="bi bi-bank"></i><div><small>{{ __('Agensi') }}</small>
                            @if($fatwa->authority)
                                <a href="{{ route('authorities.show', $fatwa->authority) }}">{{ $fatwa->authority->authority_name }}</a>
                            @else - @endif
                        </div></li>
                        <li><i class="bi bi-calendar-event"></i><div><small>{{ __('Tarikh diwartakan') }}</small>{{ $fatwa->date_issued ?? '-' }}</div></li>
                        @if($fatwa->gazette_no)
                            <li><i class="bi bi-hash"></i><div><small>{{ __('No. warta') }}</small>{{ $fatwa->gazette_no }}</div></li>
                        @endif
                        <li><i class="bi bi-tag"></i><div><small>{{ __('Kategori') }}</small>{{ $fatwa->category->category_name ?? 'Tiada kategori' }}</div></li>
                    </ul>
                </div>

                <div class="surface p-3 d-grid gap-2">
                    @auth
                        <form method="POST" action="{{ route('bookmarks.toggle', $fatwa) }}" data-bookmark class="d-grid">
                            @csrf
                            <button type="submit" class="btn {{ $saved ? 'btn-gold' : 'btn-outline-primary' }}" aria-pressed="{{ $saved ? 'true' : 'false' }}"
                                    data-label-on="{{ __('Disimpan') }}" data-label-off="{{ __('Simpan fatwa') }}">
                                <i class="bi {{ $saved ? 'bi-bookmark-check-fill' : 'bi-bookmark' }}"></i>
                                <span>{{ $saved ? __('Disimpan') : __('Simpan fatwa') }}</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary"><i class="bi bi-bookmark"></i> {{ __('Log masuk untuk simpan') }}</a>
                    @endauth
                    @if($fatwa->pdf_url)
                        <a href="{{ $fatwa->pdf_url }}" target="_blank" rel="noopener" class="btn btn-primary"><i class="bi bi-file-earmark-pdf"></i> {{ __('Buka PDF Rasmi') }}</a>
                    @endif
                    @if($fatwa->source_url && $fatwa->source_url !== $fatwa->pdf_url)
                        <a href="{{ $fatwa->source_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> {{ __('Laman Sumber') }}</a>
                    @endif
                    <a href="{{ route('fatwas.compare', $fatwa->category_id ? ['category' => $fatwa->category_id] : ['search' => \Illuminate\Support\Str::words($fatwa->title, 3, '')]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-columns-gap"></i> {{ __('Bandingkan dengan negeri lain') }}
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-ghost flex-fill" data-copy><i class="bi bi-link-45deg"></i> {{ __('Salin pautan') }}</button>
                        <button type="button" class="btn btn-ghost flex-fill" onclick="window.print()"><i class="bi bi-printer"></i> {{ __('Cetak') }}</button>
                    </div>
                </div>

                @can('admin')
                    <div class="surface p-3 d-flex gap-2">
                        <a href="{{ route('fatwas.edit', $fatwa) }}" class="btn btn-outline-primary flex-fill"><i class="bi bi-pencil"></i> {{ __('Sunting') }}</a>
                        <form action="{{ route('fatwas.destroy', $fatwa) }}" method="POST" class="flex-fill" onsubmit="return confirm('{{ __('Padam fatwa ini?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-trash"></i> {{ __('Padam') }}</button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
