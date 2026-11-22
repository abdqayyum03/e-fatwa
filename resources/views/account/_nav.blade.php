{{-- Side navigation for the account pages. --}}
<div class="surface p-2 sticky-side">
    <div class="d-flex align-items-center gap-3 p-3 border-bottom mb-2">
        <div class="avatar" style="--h: {{ crc32(auth()->user()->email) % 360 }}">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
        <div style="min-width: 0">
            <div class="fw-bold text-truncate">{{ auth()->user()->name }}</div>
            <div class="small text-muted text-truncate">{{ auth()->user()->email }}</div>
        </div>
    </div>
    <nav class="nav nav-pills flex-column gap-1">
        <a class="nav-link @if(request()->routeIs('account.edit')) active @endif" href="{{ route('account.edit') }}"><i class="bi bi-sliders me-2"></i>{{ __('Tetapan & makluman') }}</a>
        <a class="nav-link @if(request()->routeIs('bookmarks.index')) active @endif" href="{{ route('bookmarks.index') }}"><i class="bi bi-bookmark-heart me-2"></i>{{ __('Simpanan saya') }}</a>
        <a class="nav-link @if(request()->routeIs('ask.*')) active @endif" href="{{ route('ask.index') }}"><i class="bi bi-stars me-2"></i>{{ __('Tanya AI') }}</a>
    </nav>
</div>
