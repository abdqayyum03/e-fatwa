<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
    }
}
