{{--
    Shell for every /admin page: page header, side navigation, content.
    @extends('admin.layout', ['heading' => __('...'), 'subheading' => __('...'), 'icon' => 'tags'])
    Put the page itself in @section('admin'); anything in @section('hero-actions') shows top right.
--}}
@extends('layouts.app')

@section('content')
@include('partials.page-hero', [
    'title' => $heading,
    'subtitle' => $subheading ?? null,
    'icon' => $icon ?? 'speedometer2',
    'crumbs' => request()->routeIs('admin.dashboard')
        ? [__('Admin') => null]
        : [__('Admin') => route('admin.dashboard'), $heading => null],
])

<div class="container-xl page-body">
    <div class="row g-4">
        <div class="col-lg-3">@include('admin._nav')</div>
        <div class="col-lg-9 admin-main">
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('admin')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Forms marked data-confirm="..." ask before submitting (delete, merge, disable).
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('form[data-confirm]');
        if (form && !window.confirm(form.dataset.confirm)) {
            e.preventDefault();
        }
    });
</script>
@endpush
