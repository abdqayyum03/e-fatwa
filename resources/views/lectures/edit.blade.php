@extends('layouts.app')

@section('title', __('Sunting Kuliah'))

@section('content')
@include('partials.page-hero', ['title' => __('Sunting Kuliah'), 'icon' => 'pencil-square', 'crumbs' => [__('Kuliah') => route('lectures.index'), $lecture->title => route('lectures.show', $lecture), __('Sunting') => null]])

<div class="container-xl page-body">
    <form action="{{ route('lectures.update', $lecture) }}" method="POST" class="surface p-4 p-md-5 mx-auto" style="max-width: 900px">
        @csrf
        @method('PUT')
        @include('lectures._form')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('lectures.show', $lecture) }}" class="btn btn-ghost">{{ __('Batal') }}</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> {{ __('Kemas Kini') }}</button>
        </div>
    </form>
</div>
@endsection
