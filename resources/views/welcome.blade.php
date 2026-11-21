@extends('layouts.app')

@section('content')
<h1>Fatwa List</h1>
@foreach ($fatwas as $fatwa)
    <div>
        <h2>{{ $fatwa->title }}</h2>
        <a href="{{ route('fatwas.show', $fatwa->id) }}">View Details</a>
    </div>
@endforeach
@endsection
