@extends('layouts.app')

@section('title', __('Simpanan Saya'))

@section('content')
@include('partials.page-hero', [
    'title' => __('Simpanan Saya'),
    'subtitle' => __('Fatwa yang anda simpan untuk rujukan kemudian.'),
    'icon' => 'bookmark-heart',
    'crumbs' => [__('Akaun') => route('account.edit'), __('Simpanan') => null],
])

<div class="container-xl page-body">
    <div class="row g-4">
        <div class="col-lg-3">@include('account._nav')</div>
        <div class="col-lg-9">
            <p class="result-count mb-2"><strong class="text-body">{{ $fatwas->total() }}</strong> {{ __('fatwa disimpan') }}</p>
            <div class="surface overflow-hidden">
                @forelse($fatwas as $fatwa)
                    <div class="fatwa-item">
                        <div class="year"><b>{{ $fatwa->issued_year ?? '–' }}</b><small>{{ __('Tahun') }}</small></div>
                        <div class="flex-grow-1" style="min-width: 0">
                            <a href="{{ route('fatwas.show', $fatwa) }}" class="title text-decoration-none d-block">{{ $fatwa->title }}</a>
                            <div class="meta-row">
                                <x-state-pill :state="$fatwa->authority->state ?? null" />
                                @if($fatwa->category)<span class="tag">{{ $fatwa->category->category_name }}</span>@endif
                                <span class="tag"><i class="bi bi-bookmark"></i> {{ __('Disimpan :time', ['time' => $fatwa->pivot->created_at->diffForHumans()]) }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('bookmarks.toggle', $fatwa) }}" class="align-self-center">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm text-danger" aria-label="{{ __('Buang daripada simpanan') }}" title="{{ __('Buang daripada simpanan') }}">
                                <i class="bi bi-bookmark-x"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="empty">
                        <i class="bi bi-bookmark"></i>
                        <p class="mb-2">{{ __('Belum ada fatwa disimpan.') }}</p>
                        <p class="small">{{ __('Tekan butang "Simpan" pada mana-mana halaman fatwa untuk menyimpannya di sini.') }}</p>
                        <a href="{{ route('fatwas.index') }}" class="btn btn-primary btn-sm">{{ __('Teroka fatwa') }}</a>
                    </div>
                @endforelse
            </div>
            <div class="mt-4 d-flex justify-content-center">{{ $fatwas->links() }}</div>
        </div>
    </div>
</div>
@endsection
