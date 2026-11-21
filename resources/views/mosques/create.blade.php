@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Add New Mosque</h2>

    <form action="{{ route('mosques.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Mosque Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Location (City/Area)</label>
            <input type="text" name="location" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Contact Info</label>
            <input type="text" name="contact_info" class="form-control">
        </div>

        <div class="mb-3">
            <label>Latitude</label>
            <input type="text" name="latitude" class="form-control" placeholder="e.g., 3.1390" required>
        </div>

        <div class="mb-3">
            <label>Longitude</label>
            <input type="text" name="longitude" class="form-control" placeholder="e.g., 101.6869" required>
        </div>

        <button class="btn btn-success">Save Mosque</button>
    </form>
</div>
@endsection
