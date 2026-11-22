@extends('layouts.app')

@section('title', __('Tambah Pendakwah'))

@section('content')
@include('partials.page-hero', ['title' => __('Tambah Pendakwah'), 'icon' => 'person-plus', 'crumbs' => [__('Pendakwah') => route('preachers.index'), __('Tambah') => null]])

<div class="container-xl page-body">
    <form action="{{ route('preachers.store') }}" method="POST" class="surface p-4 p-md-5 mx-auto" style="max-width: 820px">
        @csrf
        @include('preachers._form')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('preachers.index') }}" class="btn btn-ghost">{{ __('Batal') }}</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> {{ __('Simpan') }}</button>
        </div>
    </form>
</div>
@endsection
