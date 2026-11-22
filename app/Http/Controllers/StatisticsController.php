<?php

namespace App\Http\Controllers;

use App\Models\Fatwa;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function __invoke()
    {
        // One grouped query gives everything per state + year.
        $rows = Fatwa::query()
            ->join('authorities', 'authorities.id', '=', 'fatwas.issuing_agency')
            ->selectRaw('authorities.state as state, fatwas.issued_year as year, count(*) as total,
                         sum(fatwas.full_text is not null) as with_text,
                         sum(fatwas.status = ?) as gazetted', ['Diwartakan'])
            ->groupBy('authorities.state', 'fatwas.issued_year')
            ->get();

        $states = $rows->pluck('state')->unique()->sort()->values();

        $perState = $states->map(fn ($state) => [
            'state' => $state,
            'total' => (int) $rows->where('state', $state)->sum('total'),
            'with_text' => (int) $rows->where('state', $state)->sum('with_text'),
            'gazetted' => (int) $rows->where('state', $state)->sum('gazetted'),
            'colour' => $this->colour($state),
        ])->sortByDesc('total')->values();

        $years = $rows->pluck('year')->filter()->unique()->sort()->values();
        $years = $years->isEmpty() ? collect() : collect(range(max(1980, $years->first()), $years->last()));

        $perYear = [
            'labels' => $years->values(),
            'series' => $states->map(fn ($state) => [
                'state' => $state,
                'colour' => $this->colour($state),
                'data' => $years->map(fn ($y) => (int) $rows->where('state', $state)->where('year', $y)->sum('total'))->values(),
            ])->values(),
        ];

        $categories = DB::table('fatwas')
            ->join('categories', 'categories.id', '=', 'fatwas.category_id')
            ->selectRaw('categories.category_name as name, count(*) as total')
            ->groupBy('categories.category_name')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $totals = [
            'fatwas' => (int) $rows->sum('total'),
            'with_text' => (int) $rows->sum('with_text'),
            'gazetted' => (int) $rows->sum('gazetted'),
            'states' => $states->count(),
            'from' => $years->first(),
            'to' => $years->last(),
            'uncategorised' => Fatwa::whereNull('category_id')->count(),
        ];

        return view('statistics', compact('perState', 'perYear', 'categories', 'totals'));
    }

    /** Same hue as the state pills (crc32 of the name), as an HSL colour. */
    private function colour(?string $state): string
    {
        return 'hsl('.(crc32((string) $state) % 360).' 55% 42%)';
    }
}
