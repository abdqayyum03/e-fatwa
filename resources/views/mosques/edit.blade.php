@extends('layouts.app')

@section('title', 'Sunting Masjid')

@section('content')
@include('partials.page-hero', ['title' => 'Sunting Masjid', 'icon' => 'pencil-square', 'crumbs' => ['Masjid' => route('mosques.index'), $mosque->name => null]])

<div class="container-xl page-body">
    <form action="{{ route('mosques.update', $mosque) }}" method="POST" class="surface p-4 p-md-5">
        @csrf
        @method('PUT')
        @include('mosques._form')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('mosques.index') }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Kemas Kini</button>
        </div>
    </form>
</div>
@endsection
