@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Search Fatwas</h2>

    <form method="GET" action="{{ route('fatwas.search') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4 mb-2">
                <input type="text" name="keyword" class="form-control" placeholder="Enter keyword..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-3 mb-2">
                <select name="category_id" class="form-control">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <select name="issuing_agency" class="form-control">
                    <option value="">All Authorities</option>
                    @foreach($authorities as $auth)
                        <option value="{{ $auth->id }}" {{ request('issuing_agency') == $auth->id ? 'selected' : '' }}>
                            {{ $auth->authority_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </div>
    </form>

    @if($fatwas->isEmpty())
        <p>No fatwas found for your search.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Authority</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($fatwas as $fatwa)
                    <tr>
                        <td>{{ $fatwa->title }}</td>
                        <td>{{ $fatwa->authority->authority_name ?? '-' }}</td>
                        <td>{{ $fatwa->category->category_name ?? '-' }}</td>
                        <td>{{ $fatwa->date_issued }}</td>
                        <td><a href="{{ route('fatwas.show', $fatwa->id) }}" class="btn btn-sm btn-outline-info">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $fatwas->withQueryString()->links() }}
    @endif
</div>
@endsection
