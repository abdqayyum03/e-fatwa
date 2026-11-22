@extends('layouts.app')

@section('title', 'Tambah Pendakwah')

@section('content')
@include('partials.page-hero', ['title' => 'Tambah Pendakwah', 'icon' => 'person-plus', 'crumbs' => ['Pendakwah' => route('preachers.index'), 'Tambah' => null]])

<div class="container-xl page-body">
    <form action="{{ route('preachers.store') }}" method="POST" class="surface p-4 p-md-5 mx-auto" style="max-width: 820px">
        @csrf
        @include('preachers._form')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('preachers.index') }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Simpan</button>
        </div>
    </form>
</div>
@endsection
