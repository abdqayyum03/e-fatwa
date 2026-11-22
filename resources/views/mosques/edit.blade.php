@extends('layouts.app')

@section('title', __('Sunting Masjid'))

@section('content')
@include('partials.page-hero', ['title' => __('Sunting Masjid'), 'icon' => 'pencil-square', 'crumbs' => [__('Masjid') => route('mosques.index'), $mosque->name => null]])

<div class="container-xl page-body">
    <form action="{{ route('mosques.update', $mosque) }}" method="POST" class="surface p-4 p-md-5">
        @csrf
        @method('PUT')
        @include('mosques._form')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('mosques.index') }}" class="btn btn-ghost">{{ __('Batal') }}</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> {{ __('Kemas Kini') }}</button>
        </div>
    </form>
</div>
@endsection
