{{-- Side navigation for the admin pages. --}}
@php
    $unreadFeedback = \App\Models\Feedback::whereNull('handled_at')->count();
    $links = [
        ['admin.dashboard', 'admin.dashboard', 'speedometer2', __('Gambaran keseluruhan'), null],
        ['admin.quality.index', 'admin.quality.*', 'clipboard-check', __('Kualiti data'), null],
        ['admin.categories.index', 'admin.categories.*', 'tags', __('Kategori'), null],
        ['admin.imports.index', 'admin.imports.*', 'cloud-download', __('Import'), null],
        ['admin.feedback.index', 'admin.feedback.*', 'chat-left-text', __('Maklum balas'), $unreadFeedback ?: null],
        ['admin.users.index', 'admin.users.*', 'people', __('Pengguna'), null],
        ['admin.authorities.index', 'admin.authorities.*', 'bank', __('Agensi'), null],
    ];
@endphp
<div class="surface p-2 sticky-side">
    <div class="d-flex align-items-center gap-2 px-3 pt-3 pb-2 border-bottom mb-2">
        <i class="bi bi-shield-lock text-primary"></i>
        <span class="fw-bold">{{ __('Kawasan admin') }}</span>
    </div>
    <nav class="nav nav-pills flex-column gap-1" aria-label="{{ __('Menu admin') }}">
        @foreach($links as [$route, $pattern, $icon, $label, $badge])
            <a class="nav-link d-flex align-items-center @if(request()->routeIs($pattern)) active @endif" href="{{ route($route) }}"
               @if(request()->routeIs($pattern)) aria-current="page" @endif>
                <i class="bi bi-{{ $icon }} me-2"></i>{{ $label }}
                @if($badge)<span class="badge rounded-pill text-bg-warning ms-auto">{{ $badge }}</span>@endif
            </a>
        @endforeach
    </nav>
</div>
