<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface language: the visitor's choice (session), else their account
 * setting, else the app default (Malay). Interface text uses the Malay
 * string as the translation key; English lives in lang/en.json.
 */
class SetLocale
{
    public const SUPPORTED = ['ms', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? $request->user()?->locale
            ?? config('app.locale');

        if (in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
            \Illuminate\Support\Carbon::setLocale($locale);
        }

        return $next($request);
    }
}
