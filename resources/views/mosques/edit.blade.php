@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit Mosque</h2>

    <form action="{{ route('mosques.update', $mosque->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Mosque Name</label>
            <input type="text" name="name" class="form-control" value="{{ $mosque->name }}" required>
        </div>

        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control" value="{{ $mosque->location }}" required>
        </div>

        <div class="mb-3">
            <label>Contact Info</label>
            <input type="text" name="contact_info" class="form-control" value="{{ $mosque->contact_info }}">
        </div>

        <div class="mb-3">
            <label>Latitude</label>
            <input type="text" name="latitude" class="form-control" value="{{ $mosque->latitude }}" required>
        </div>

        <div class="mb-3">
            <label>Longitude</label>
            <input type="text" name="longitude" class="form-control" value="{{ $mosque->longitude }}" required>
        </div>

        <button class="btn btn-primary">Update Mosque</button>
    </form>
</div>
@endsection
