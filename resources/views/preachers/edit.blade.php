@extends('layouts.app')

@section('title', 'Sunting Pendakwah')

@section('content')
@include('partials.page-hero', ['title' => 'Sunting Pendakwah', 'icon' => 'pencil-square', 'crumbs' => ['Pendakwah' => route('preachers.index'), $preacher->preacher_name => route('preachers.show', $preacher), 'Sunting' => null]])

<div class="container-xl page-body">
    <form action="{{ route('preachers.update', $preacher) }}" method="POST" class="surface p-4 p-md-5 mx-auto" style="max-width: 820px">
        @csrf
        @method('PUT')
        @include('preachers._form')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('preachers.show', $preacher) }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Kemas Kini</button>
        </div>
    </form>
</div>
@endsection
