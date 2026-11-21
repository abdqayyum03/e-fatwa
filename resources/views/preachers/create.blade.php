@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Add New Preacher</h2>

    <form action="{{ route('preachers.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="preacher_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Certification Details</label>
            <textarea name="certification_details" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control">
        </div>

        <div class="mb-3">
            <label>State</label>
            <input type="text" name="state" class="form-control">
        </div>

        <div class="mb-3">
            <label>Issuing Authority</label>
            <select name="authority_id" class="form-control">
                <option value="">-- Optional --</option>
                @foreach($authorities as $authority)
                    <option value="{{ $authority->id }}">{{ $authority->authority_name }}</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-success">Save</button>
    </form>
</div>
@endsection
