@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Add New Fatwa</h2>

    <form action="{{ route('fatwas.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="title" class="form-label">Fatwa Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Fatwa Content</label>
            <textarea name="content" class="form-control" rows="5" required>{{ old('content') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="issuing_agency" class="form-label">Issuing Agency</label>
            <select name="issuing_agency" class="form-control custom-select" required>
                <option value="">-- Select Authority --</option>
                @foreach($authorities as $authority)
                    <option value="{{ $authority->id }}">{{ $authority->authority_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">Fatwa Category</label>
            <select name="category_id" class="form-control custom-select">
                <option value="">-- Optional --</option>
                <option value="1">Akidah & Ibadah</option>
                <option value="2">Muamalat (Kewangan/Perbankan)</option>
                <option value="3">Sosial & Kekeluargaan</option>
                <option value="4">Pendidikan & Dakwah</option>
                <option value="5">Kesihatan & Perubatan</option>
            </select>
        </div>
        

        <div class="mb-3">
            <label for="date_issued" class="form-label">Date Issued</label>
            <input type="date" name="date_issued" class="form-control" required>
        </div>

        <button class="btn btn-success">Save Fatwa</button>
    </form>
</div>
@endsection
