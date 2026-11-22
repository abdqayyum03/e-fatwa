@extends('layouts.app')

@section('title', __('Sunting Fatwa'))

@section('content')
@include('partials.page-hero', ['title' => __('Sunting Fatwa'), 'icon' => 'pencil-square', 'crumbs' => [__('Fatwa') => route('fatwas.index'), $fatwa->title => route('fatwas.show', $fatwa), __('Sunting') => null]])

<div class="container-xl page-body">
    <form action="{{ route('fatwas.update', $fatwa) }}" method="POST" class="surface p-4 p-md-5">
        @csrf
        @method('PUT')
        @include('fatwas._form')
        <div class="d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('fatwas.show', $fatwa) }}" class="btn btn-ghost">{{ __('Batal') }}</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> {{ __('Kemas Kini Fatwa') }}</button>
        </div>
    </form>
</div>
@endsection
