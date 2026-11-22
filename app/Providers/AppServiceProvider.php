<?php

namespace App\Providers;

use App\Models\User;
use App\Support\CaBundle;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Enable Bootstrap 5 pagination style
        Paginator::useBootstrapFive();

        // Only admins may create, edit or delete records.
        // Used by the "can:admin" route middleware and @can('admin') in views.
        Gate::define('admin', fn (User $user) => $user->is_admin);

        // Every outgoing request (scraper, e-Solat) verifies HTTPS with our own
        // CA bundle instead of relying on the machine's php.ini.
        Http::globalOptions(['verify' => CaBundle::path()]);

        // "Tanya AI" costs money per question: limit each user (or IP) per minute and per day.
        RateLimiter::for('ask', fn (Request $request) => [
            Limit::perMinute(5)->by('ask-min:'.($request->user()?->id ?: $request->ip())),
            Limit::perDay(40)->by('ask-day:'.($request->user()?->id ?: $request->ip()))
                ->response(fn () => response()->json(['message' => __('Had harian soalan telah dicapai. Sila cuba esok.')], 429)),
        ]);
    }
}
