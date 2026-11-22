<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /** /bahasa/ms or /bahasa/en: remember the choice and go back to the same page. */
    public function __invoke(Request $request, string $locale)
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);

        $request->session()->put('locale', $locale);
        $request->user()?->update(['locale' => $locale]);

        return redirect()->back(fallback: route('dashboard'));
    }
}
