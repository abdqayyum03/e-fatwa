<?php

namespace App\Services;

use App\Models\CategoryRule;
use App\Models\Fatwa;

/**
 * Applies the keyword rules to fatwas that have no category. Only titles are
 * matched (full texts mention many topics in passing). The lowest-priority
 * matching rule wins; if two different categories match at that same priority,
 * the fatwa is left uncategorised rather than guessed.
 */
class CategoryRules
{
    /**
     * @return array{assigned: array<string, list<array{id:int, title:string}>>, ambiguous: int, unmatched: int}
     */
    public function apply(bool $dryRun = true): array
    {
        $rules = CategoryRule::with('category')->orderBy('priority')->get();
        $result = ['assigned' => [], 'ambiguous' => 0, 'unmatched' => 0];

        Fatwa::whereNull('category_id')->select(['id', 'title'])->chunkById(500, function ($fatwas) use ($rules, $dryRun, &$result) {
            foreach ($fatwas as $fatwa) {
                $matches = $rules->filter(fn (CategoryRule $rule) => $rule->matches($fatwa->title));

                if ($matches->isEmpty()) {
                    $result['unmatched']++;

                    continue;
                }

                $best = $matches->where('priority', $matches->min('priority'));

                if ($best->pluck('category_id')->unique()->count() > 1) {
                    $result['ambiguous']++;

                    continue;
                }

                $rule = $best->first();
                $result['assigned'][$rule->category->category_name][] = ['id' => $fatwa->id, 'title' => $fatwa->title];

                if (! $dryRun) {
                    Fatwa::whereKey($fatwa->id)->update(['category_id' => $rule->category_id]);
                }
            }
        });

        ksort($result['assigned']);

        return $result;
    }
}
