@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit Fatwa</h2>

    <form action="{{ route('fatwas.update', $fatwa->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="title" class="form-label">Fatwa Title</label>
            <input type="text" name="title" class="form-control" value="{{ $fatwa->title }}" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Fatwa Content</label>
            <textarea name="content" class="form-control" rows="5" required>{{ $fatwa->content }}</textarea>
        </div>

        <div class="mb-3">
            <label for="issuing_agency" class="form-label">Issuing Agency</label>
            <select name="issuing_agency" class="form-control" required>
                @foreach($authorities as $authority)
                    <option value="{{ $authority->id }}" {{ $fatwa->issuing_agency == $authority->id ? 'selected' : '' }}>{{ $authority->authority_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">Fatwa Category</label>
            <select name="category_id" class="form-control">
                <option value="">-- Optional --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $fatwa->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="date_issued" class="form-label">Date Issued</label>
            <input type="date" name="date_issued" class="form-control" value="{{ $fatwa->date_issued }}" required>
        </div>

        <button class="btn btn-primary">Update Fatwa</button>
    </form>
</div>
@endsection
