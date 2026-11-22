@extends('layouts.app')

@section('title', 'Tambah Fatwa')

@section('content')
@include('partials.page-hero', ['title' => 'Tambah Fatwa', 'icon' => 'journal-plus', 'crumbs' => ['Fatwa' => route('fatwas.index'), 'Tambah' => null]])

<div class="container-xl page-body">
    <form action="{{ route('fatwas.store') }}" method="POST" class="surface p-4 p-md-5">
        @csrf
        @include('fatwas._form')
        <div class="d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('fatwas.index') }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Simpan Fatwa</button>
        </div>
    </form>
</div>
@endsection
