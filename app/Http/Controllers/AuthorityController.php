<?php

namespace App\Http\Controllers;

use App\Models\Authority;

class AuthorityController extends Controller
{
    /**
     * Display a listing of the authorities.
     */
    public function index()
    {
        $authorities = Authority::withCount(['fatwas', 'fatwas as text_count' => fn ($q) => $q->whereNotNull('full_text')])
            ->orderByDesc('fatwas_count')
            ->orderBy('authority_name')
            ->get();

        return view('authorities.index', compact('authorities'));
    }

    /**
     * Display the specified authority with its latest fatwas.
     */
    public function show(Authority $authority)
    {
        $authority->loadCount('fatwas');

        $latest = $authority->fatwas()
            ->with('category')
            ->orderByDesc('issued_year')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $byYear = $authority->fatwas()
            ->whereNotNull('issued_year')
            ->selectRaw('issued_year, count(*) as total')
            ->groupBy('issued_year')
            ->orderByDesc('issued_year')
            ->limit(12)
            ->pluck('total', 'issued_year');

        return view('authorities.show', compact('authority', 'latest', 'byYear'));
    }
}
