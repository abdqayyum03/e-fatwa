@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Search Certified Preachers</h2>

    <form method="GET" action="{{ route('preachers.search') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4 mb-2">
                <input type="text" name="name" class="form-control" placeholder="Search by name..." value="{{ request('name') }}">
            </div>
            <div class="col-md-4 mb-2">
                <input type="text" name="location" class="form-control" placeholder="Location..." value="{{ request('location') }}">
            </div>
            <div class="col-md-3 mb-2">
                <select name="state" class="form-control">
                    <option value="">All States</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 mb-2">
                <button type="submit" class="btn btn-primary w-100">Go</button>
            </div>
        </div>
    </form>

    @if($preachers->isEmpty())
        <p>No certified preachers found.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>State</th>
                    <th>Location</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($preachers as $preacher)
                <tr>
                    <td>{{ $preacher->preacher_name }}</td>
                    <td>{{ $preacher->state }}</td>
                    <td>{{ $preacher->location }}</td>
                    <td><a href="{{ route('preachers.show', $preacher->id) }}" class="btn btn-sm btn-outline-info">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $preachers->withQueryString()->links() }}
    @endif
</div>
@endsection
