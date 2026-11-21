<?php

namespace App\Http\Controllers;

use App\Models\Mosque;
use Illuminate\Http\Request;

class MosqueController extends Controller
{
    public function index(Request $request)
{
    $query = Mosque::query();

    // Apply search filter
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%$search%")
              ->orWhere('address', 'like', "%$search%");
        });
    }

    // Apply location filter
    if ($request->filled('location')) {
        $query->where('location', $request->location);
    }

    // Get paginated results
    $mosques = $query->orderBy('name')->paginate(10);

    // Get list of distinct locations for the filter dropdown
    $locations = Mosque::select('location')
                ->distinct()
                ->orderBy('location')
                ->pluck('location');

    return view('mosques.index', compact('mosques', 'locations'));
}
    // Other CRUD methods can be added as needed
}
