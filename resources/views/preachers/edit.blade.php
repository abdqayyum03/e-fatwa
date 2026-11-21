@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit Preacher</h2>

    <form action="{{ route('preachers.update', $preacher->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="preacher_name" class="form-control" value="{{ $preacher->preacher_name }}" required>
        </div>

        <div class="mb-3">
            <label>Certification Details</label>
            <textarea name="certification_details" class="form-control" rows="3">{{ $preacher->certification_details }}</textarea>
        </div>

        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control" value="{{ $preacher->location }}">
        </div>

        <div class="mb-3">
            <label>State</label>
            <input type="text" name="state" class="form-control" value="{{ $preacher->state }}">
        </div>

        <div class="mb-3">
            <label>Issuing Authority</label>
            <select name="authority_id" class="form-control">
                <option value="">-- Optional --</option>
                @foreach($authorities as $authority)
                    <option value="{{ $authority->id }}" {{ $preacher->authority_id == $authority->id ? 'selected' : '' }}>
                        {{ $authority->authority_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
