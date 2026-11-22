<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fatwa;
use App\Models\Feedback;
use App\Models\ImportRun;
use App\Models\User;
use App\Scrapers\Sources;
use App\Services\FatwaQuality;

/** /admin: the overview page. */
class DashboardController extends Controller
{
    public function __invoke(FatwaQuality $quality)
    {
        $weekAgo = now()->subWeek();

        // Latest run per source, shown next to its state.
        $latestRuns = ImportRun::whereIn('id', ImportRun::selectRaw('max(id)')->groupBy('source'))->get()->keyBy('source');

        return view('admin.dashboard', [
            'totals' => [
                'fatwas' => Fatwa::count(),
                'with_text' => Fatwa::whereNotNull('full_text')->count(),
                'ocr' => Fatwa::where('text_from_ocr', true)->count(),
                'with_decision' => Fatwa::whereNotNull('decision')->count(),
                'users' => User::count(),
                'admins' => User::where('is_admin', true)->count(),
            ],
            'newFatwas' => Fatwa::with('authority')->where('created_at', '>=', $weekAgo)->latest()->take(8)->get(),
            'newFatwaCount' => Fatwa::where('created_at', '>=', $weekAgo)->count(),
            'newUsers' => User::where('created_at', '>=', $weekAgo)->latest()->take(5)->get(),
            'unreadFeedback' => Feedback::whereNull('handled_at')->latest()->take(5)->get(),
            'unreadFeedbackCount' => Feedback::whereNull('handled_at')->count(),
            'quality' => $quality->counts(),
            'sources' => Sources::states(),
            'latestRuns' => $latestRuns,
        ]);
    }
}
