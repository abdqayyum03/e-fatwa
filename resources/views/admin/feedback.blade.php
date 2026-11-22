@extends('admin.layout', [
    'heading' => __('Maklum balas'),
    'subheading' => __('Mesej daripada borang maklum balas awam.'),
    'icon' => 'chat-left-text',
])

@section('title', __('Maklum balas'))

@section('admin')
<ul class="nav nav-pills gap-1 mb-3">
    @foreach(['baru' => __('Baharu'), 'selesai' => __('Selesai'), 'semua' => __('Semua')] as $key => $label)
        <li class="nav-item">
            <a class="nav-link @if($show === $key) active @endif" href="{{ route('admin.feedback.index', ['papar' => $key]) }}">
                {{ $label }} <span class="badge rounded-pill {{ $show === $key ? 'text-bg-light' : 'text-bg-secondary' }} ms-1">{{ $counts[$key] }}</span>
            </a>
        </li>
    @endforeach
</ul>

@forelse($feedback as $item)
    <article class="surface p-4 mb-3 @if($item->handled_at) opacity-75 @endif">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
            <div>
                <strong>{{ $item->name }}</strong>
                <div class="small text-muted">
                    <a href="mailto:{{ $item->email }}">{{ $item->email }}</a>
                    · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $item->phone_number) }}">{{ $item->phone_number }}</a>
                </div>
            </div>
            <div class="text-end small text-muted">
                <div title="{{ $item->created_at->format('d/m/Y H:i') }}">{{ $item->created_at->diffForHumans() }}</div>
                @if($item->handled_at)<span class="badge text-bg-success"><i class="bi bi-check2"></i> {{ __('Selesai') }}</span>@endif
            </div>
        </div>
        <p class="mb-3" style="white-space: pre-line">{{ $item->content }}</p>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.feedback.update', $item) }}">
                @csrf @method('PATCH')
                <button class="btn btn-sm {{ $item->handled_at ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                    @if($item->handled_at)
                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Tanda sebagai baharu') }}
                    @else
                        <i class="bi bi-check2"></i> {{ __('Tanda selesai') }}
                    @endif
                </button>
            </form>
            <form method="POST" action="{{ route('admin.feedback.destroy', $item) }}" data-confirm="{{ __('Padam maklum balas ini? Tindakan ini tidak boleh dibatalkan.') }}">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> {{ __('Padam') }}</button>
            </form>
        </div>
    </article>
@empty
    <div class="surface"><div class="empty"><i class="bi bi-inbox"></i>{{ __('Tiada maklum balas di sini.') }}</div></div>
@endforelse

<div class="mt-3">{{ $feedback->links() }}</div>
@endsection
