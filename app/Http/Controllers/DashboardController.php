<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\Category;
use App\Models\Fatwa;
use App\Models\Mosque;
use App\Models\Preacher;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $perState = Authority::withCount('fatwas')
            ->having('fatwas_count', '>', 0)
            ->orderByDesc('fatwas_count')
            ->get()
            ->groupBy('state')
            ->map(fn ($group) => $group->sum('fatwas_count'));

        $stats = [
            'fatwas' => Fatwa::count(),
            'with_text' => Fatwa::whereNotNull('full_text')->count(),
            'states' => $perState->count(),
            'authorities' => Authority::count(),
            'mosques' => Mosque::count(),
            'preachers' => Preacher::count(),
        ];

        $latest = Fatwa::with('authority', 'category')
            ->orderByDesc('issued_year')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $categories = Category::withCount('fatwas')->orderByDesc('fatwas_count')->limit(8)->get();

        return view('dashboard', compact('stats', 'perState', 'latest', 'categories'));
    }
}
