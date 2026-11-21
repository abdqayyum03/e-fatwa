<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use Illuminate\Http\Request;

class AuthorityController extends Controller
{
    /**
     * Display a listing of the authorities.
     */
    public function index()
    {
        $authorities = Authority::all();
        return view('authorities.index', compact('authorities'));
    }

    /**
     * Display the specified authority.
     */
    public function show(Authority $authority)
    {
        return view('authorities.show', compact('authority'));
    }
}
