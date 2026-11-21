@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>{{ $preacher->preacher_name }}</h2>

    <p><strong>State:</strong> {{ $preacher->state }}</p>
    <p><strong>Location:</strong> {{ $preacher->location }}</p>
    <p><strong>Certification:</strong><br> {!! nl2br(e($preacher->certification_details)) !!}</p>
    <p><strong>Authority:</strong> {{ $preacher->authority->authority_name ?? '-' }}</p>

    <a href="{{ route('preachers.index') }}" class="btn btn-secondary mt-3">Back to List</a>
</div>
@endsection
