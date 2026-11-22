@extends('layouts.app')

@section('title', __('Tambah Kuliah'))

@section('content')
@include('partials.page-hero', ['title' => __('Tambah Kuliah'), 'icon' => 'calendar-plus', 'crumbs' => [__('Kuliah') => route('lectures.index'), __('Tambah') => null]])

<div class="container-xl page-body">
    <form action="{{ route('lectures.store') }}" method="POST" class="surface p-4 p-md-5 mx-auto" style="max-width: 900px">
        @csrf
        @include('lectures._form')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('lectures.index') }}" class="btn btn-ghost">{{ __('Batal') }}</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> {{ __('Simpan') }}</button>
        </div>
    </form>
</div>
@endsection
