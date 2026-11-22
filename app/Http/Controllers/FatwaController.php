<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\Category;
use App\Models\Fatwa;
use Illuminate\Http\Request;

class FatwaController extends Controller
{
    /** Explore + search: keyword, state, year, category and authority filters. */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'state', 'year', 'category', 'agency']);

        $fatwas = Fatwa::with('authority', 'category')
            ->filter($filters)
            ->orderByDesc('issued_year')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('fatwas.index', $this->filterOptions() + compact('fatwas', 'filters'));
    }

    /** Live search suggestions for the search boxes (JSON, max 6). */
    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q'));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Fatwa::with('authority:id,state')
            ->select('id', 'title', 'issued_year', 'issuing_agency')
            ->search($q)
            ->orderByDesc('issued_year')
            ->limit(6)
            ->get()
            ->map(fn (Fatwa $f) => [
                'title' => $f->title,
                'state' => $f->authority->state ?? null,
                'year' => $f->issued_year,
                'url' => route('fatwas.show', $f),
            ]);

        return response()->json($results);
    }

    /** Old search page: its filters now live on the main list. */
    public function search(Request $request)
    {
        return redirect()->route('fatwas.index', array_filter([
            'search' => $request->input('keyword'),
            'category' => $request->input('category_id'),
            'agency' => $request->input('issuing_agency'),
        ]));
    }

    /**
     * Side-by-side comparison: the same keyword or category across states,
     * one column per state that has matching fatwas.
     */
    public function compare(Request $request)
    {
        $filters = $request->only(['search', 'category']);
        $columns = collect();

        if (array_filter($filters)) {
            $columns = Fatwa::with('authority', 'category')
                ->filter($filters)
                ->whereNotNull('issuing_agency')
                ->orderByDesc('issued_year')
                ->limit(300)
                ->get()
                ->groupBy(fn (Fatwa $f) => $f->authority->state ?? 'Lain-lain')
                ->sortKeys();
        }

        $categories = Category::orderBy('category_name')->get();
        $statesWithFatwas = Authority::has('fatwas')->orderBy('state')->pluck('state')->unique()->values();

        return view('fatwas.compare', compact('columns', 'filters', 'categories', 'statesWithFatwas'));
    }

    public function create()
    {
        return view('fatwas.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        Fatwa::create($this->validated($request));

        return redirect()->route('fatwas.index')->with('success', 'Fatwa berjaya disimpan.');
    }

    public function show(Fatwa $fatwa)
    {
        $fatwa->load('authority', 'category');

        // Same-topic fatwas from other states, for the "compare" shortcut.
        $related = $fatwa->category_id
            ? Fatwa::with('authority')
                ->where('category_id', $fatwa->category_id)
                ->where('id', '!=', $fatwa->id)
                ->latest('issued_year')
                ->limit(5)
                ->get()
            : collect();

        return view('fatwas.show', compact('fatwa', 'related'));
    }

    public function edit(Fatwa $fatwa)
    {
        return view('fatwas.edit', $this->formOptions() + compact('fatwa'));
    }

    public function update(Request $request, Fatwa $fatwa)
    {
        $fatwa->update($this->validated($request, $fatwa));

        return redirect()->route('fatwas.show', $fatwa)->with('success', 'Fatwa berjaya dikemas kini.');
    }

    public function destroy(Fatwa $fatwa)
    {
        $fatwa->delete();

        return redirect()->route('fatwas.index')->with('success', 'Fatwa berjaya dipadam.');
    }

    private function validated(Request $request, ?Fatwa $fatwa = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:500',
            'issuing_agency' => 'required|exists:authorities,id',
            'category_id' => 'nullable|exists:categories,id',
            'date_issued' => 'required|string|max:50',
            'gazette_no' => 'nullable|string|max:100',
            'status' => 'nullable|in:Diwartakan,Tidak Diwartakan',
            'pdf_url' => 'nullable|url|max:500',
            'source_url' => 'nullable|url|max:500|unique:fatwas,source_url'.($fatwa ? ",{$fatwa->id}" : ''),
            'content' => 'nullable|string',
            'full_text' => 'nullable|string',
        ]);
    }

    private function filterOptions(): array
    {
        return [
            'states' => Authority::has('fatwas')->orderBy('state')->pluck('state')->unique()->values(),
            'years' => Fatwa::whereNotNull('issued_year')->distinct()->orderByDesc('issued_year')->pluck('issued_year'),
            'categories' => Category::orderBy('category_name')->get(),
            'agencies' => Authority::orderBy('authority_name')->get(),
        ];
    }

    private function formOptions(): array
    {
        return [
            'authorities' => Authority::orderBy('authority_name')->get(),
            'categories' => Category::orderBy('category_name')->get(),
        ];
    }
}
