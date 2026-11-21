<?php

namespace App\Http\Controllers;

use App\Models\Fatwa;
use App\Models\Authority;
use App\Models\Category;
use Illuminate\Http\Request;

class FatwaController extends Controller
{
    public function index(Request $request)
    {
        $query = Fatwa::with('authority');

        // Search by keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('content', 'like', "%$search%");
            });
        }

        // Filter by year (string-based date_issued)
        if ($request->filled('year')) {
            $query->where('date_issued', $request->year);
        }

        // Filter by agency
        if ($request->filled('agency')) {
            $query->where('issuing_agency', $request->agency);
        }

        // Paginate results (15 per page)
        $fatwas = $query->orderByDesc('date_issued')->paginate(15);

        // Dropdown data: distinct years (strings)
        $years = Fatwa::select('date_issued')
                    ->distinct()
                    ->orderByDesc('date_issued')
                    ->pluck('date_issued');

        // Dropdown data: agencies
        $agencies = Authority::orderBy('authority_name')->get();

        return view('fatwas.index', compact('fatwas', 'years', 'agencies'));
    }

    public function create()
    {
        $authorities = Authority::all();
        $categories = Category::all();
        return view('fatwas.create', compact('authorities', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'issuing_agency' => 'required|integer',
            'category_id' => 'nullable|integer',
            'date_issued' => 'required|string', // Expecting string like "2020"
        ]);

        Fatwa::create([
            'title' => $request->title,
            'content' => $request->content,
            'issuing_agency' => $request->issuing_agency,
            'category_id' => $request->category_id,
            'date_issued' => $request->date_issued,
        ]);

        return redirect()->route('fatwas.index')->with('success', 'Fatwa saved successfully.');
    }

    public function show(Fatwa $fatwa)
    {
        return view('fatwas.show', compact('fatwa'));
    }

    public function edit(Fatwa $fatwa)
    {
        $authorities = Authority::all();
        $categories = Category::all();
        return view('fatwas.edit', compact('fatwa', 'authorities', 'categories'));
    }

    public function update(Request $request, Fatwa $fatwa)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'issuing_agency' => 'required|exists:authorities,id',
            'date_issued' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $fatwa->update($validated);

        return redirect()->route('fatwas.index')->with('success', 'Fatwa updated successfully.');
    }

    public function destroy(Fatwa $fatwa)
    {
        $fatwa->delete();
        return redirect()->route('fatwas.index')->with('success', 'Fatwa deleted.');
    }

    public function search(Request $request)
    {
        $query = Fatwa::with('authority', 'category');

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%')
                  ->orWhere('content', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('issuing_agency')) {
            $query->where('issuing_agency', $request->issuing_agency);
        }

        $fatwas = $query->latest()->paginate(10);
        $categories = Category::all();
        $authorities = Authority::all();

        return view('fatwas.search', compact('fatwas', 'categories', 'authorities'));
    }
}
