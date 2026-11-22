@extends('layouts.app')

@section('title', 'Sunting Fatwa')

@section('content')
@include('partials.page-hero', ['title' => 'Sunting Fatwa', 'icon' => 'pencil-square', 'crumbs' => ['Fatwa' => route('fatwas.index'), $fatwa->title => route('fatwas.show', $fatwa), 'Sunting' => null]])

<div class="container-xl page-body">
    <form action="{{ route('fatwas.update', $fatwa) }}" method="POST" class="surface p-4 p-md-5">
        @csrf
        @method('PUT')
        @include('fatwas._form')
        <div class="d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('fatwas.show', $fatwa) }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Kemas Kini Fatwa</button>
        </div>
    </form>
</div>
@endsection
