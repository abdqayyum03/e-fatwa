<?php

namespace App\Http\Controllers;

use App\Models\Preacher;
use App\Models\Authority;
use Illuminate\Http\Request;

class PreacherController extends Controller
{
    // Admin or Public Index (can be reused)
    public function index()
    {
        $preachers = Preacher::with('authority')->latest()->paginate(10);
        return view('preachers.index', compact('preachers'));
    }

    // Public search page
    public function search(Request $request)
    {
        $query = Preacher::with('authority');

        if ($request->filled('name')) {
            $query->where('preacher_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        $preachers = $query->latest()->paginate(10);
        $states = Preacher::select('state')->distinct()->pluck('state');

        return view('preachers.search', compact('preachers', 'states'));
    }

    public function create()
    {
        $authorities = Authority::all();
        return view('preachers.create', compact('authorities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'preacher_name' => 'required|string|max:255',
            'certification_details' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'authority_id' => 'nullable|exists:authorities,id',
        ]);

        Preacher::create($validated);

        return redirect()->route('preachers.index')->with('success', 'Preacher created successfully.');
    }

    public function show(Preacher $preacher)
    {
        return view('preachers.show', compact('preacher'));
    }

    public function edit(Preacher $preacher)
    {
        $authorities = Authority::all();
        return view('preachers.edit', compact('preacher', 'authorities'));
    }

    public function update(Request $request, Preacher $preacher)
    {
        $validated = $request->validate([
            'preacher_name' => 'required|string|max:255',
            'certification_details' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'authority_id' => 'nullable|exists:authorities,id',
        ]);

        $preacher->update($validated);

        return redirect()->route('preachers.index')->with('success', 'Preacher updated successfully.');
    }

    public function destroy(Preacher $preacher)
    {
        $preacher->delete();
        return redirect()->route('preachers.index')->with('success', 'Preacher deleted successfully.');
    }
}
