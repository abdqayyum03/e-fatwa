<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\Preacher;
use Illuminate\Http\Request;

class PreacherController extends Controller
{
    // Public directory with search (name/location) and state filter
    public function index(Request $request)
    {
        $filters = $request->only(['name', 'location', 'state']);

        $preachers = Preacher::with('authority')
            ->when($filters['name'] ?? null, fn ($q, $v) => $q->where('preacher_name', 'like', "%{$v}%"))
            ->when($filters['location'] ?? null, fn ($q, $v) => $q->where('location', 'like', "%{$v}%"))
            ->when($filters['state'] ?? null, fn ($q, $v) => $q->where('state', $v))
            ->orderBy('preacher_name')
            ->paginate(12)
            ->withQueryString();

        $states = Preacher::whereNotNull('state')->distinct()->orderBy('state')->pluck('state');

        return view('preachers.index', compact('preachers', 'states', 'filters'));
    }

    // Old search URL: same page, same filters
    public function search(Request $request)
    {
        return $this->index($request);
    }

    public function create()
    {
        $authorities = Authority::orderBy('authority_name')->get();

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

        return redirect()->route('preachers.index')->with('success', 'Pendakwah berjaya ditambah.');
    }

    public function show(Preacher $preacher)
    {
        $lectures = $preacher->lectures()->with('mosque')->upcoming()->limit(6)->get();

        return view('preachers.show', compact('preacher', 'lectures'));
    }

    public function edit(Preacher $preacher)
    {
        $authorities = Authority::orderBy('authority_name')->get();

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

        return redirect()->route('preachers.index')->with('success', 'Pendakwah berjaya dikemas kini.');
    }

    public function destroy(Preacher $preacher)
    {
        $preacher->delete();

        return redirect()->route('preachers.index')->with('success', 'Pendakwah berjaya dipadam.');
    }
}
