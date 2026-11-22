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
                    ->orWhere('location', 'like', "%$search%");
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

    public function map()
    {
        $mosques = Mosque::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount(['lectures as upcoming_lectures' => fn ($q) => $q->where('starts_at', '>=', now()->startOfDay())])
            ->orderBy('name')
            ->get(['id', 'name', 'location', 'contact_info', 'latitude', 'longitude']);

        return view('mosques.map', compact('mosques'));
    }

    public function create()
    {
        return view('mosques.create');
    }

    public function store(Request $request)
    {
        Mosque::create($this->validated($request));

        return redirect()->route('mosques.index')->with('success', 'Masjid berjaya ditambah.');
    }

    public function edit(Mosque $mosque)
    {
        return view('mosques.edit', compact('mosque'));
    }

    public function update(Request $request, Mosque $mosque)
    {
        $mosque->update($this->validated($request));

        return redirect()->route('mosques.index')->with('success', 'Masjid berjaya dikemas kini.');
    }

    public function destroy(Mosque $mosque)
    {
        $mosque->delete();

        return redirect()->route('mosques.index')->with('success', 'Masjid berjaya dipadam.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
    }
}
