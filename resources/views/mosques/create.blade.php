@extends('layouts.app')

@section('title', __('Tambah Masjid'))

@section('content')
@include('partials.page-hero', ['title' => __('Tambah Masjid'), 'icon' => 'building-add', 'crumbs' => [__('Masjid') => route('mosques.index'), __('Tambah') => null]])

<div class="container-xl page-body">
    <form action="{{ route('mosques.store') }}" method="POST" class="surface p-4 p-md-5">
        @csrf
        @include('mosques._form')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('mosques.index') }}" class="btn btn-ghost">{{ __('Batal') }}</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> {{ __('Simpan') }}</button>
        </div>
    </form>
</div>
@endsection
