<?php

namespace App\Services;

use App\Models\Fatwa;
use Illuminate\Database\Eloquent\Builder;

/**
 * The admin data-quality queue: which fatwas need a human look, by issue.
 * Fatwas an admin marked as checked (quality_checked_at) drop out of every list.
 */
class FatwaQuality
{
    /** issue key => [Malay label, Bootstrap icon] */
    public const ISSUES = [
        'no_text' => ['Tiada teks', 'file-earmark-x'],
        'no_category' => ['Tiada kategori', 'tags'],
        'no_date' => ['Tiada tarikh', 'calendar-x'],
        'no_decision' => ['Keputusan tidak dikesan', 'check2-circle'],
        'garbled' => ['Teks OCR bercelaru', 'eye-slash'],
        'duplicates' => ['Kemungkinan pendua', 'files'],
    ];

    /** @var array<string, list<int>> */
    private array $idCache = [];

    /** @return array<string, int> issue => number of unchecked fatwas */
    public function counts(): array
    {
        return collect(array_keys(self::ISSUES))
            ->mapWithKeys(fn ($issue) => [$issue => $this->query($issue)->count()])
            ->all();
    }

    /** Unchecked fatwas with this issue, optionally in one state. */
    public function query(string $issue, ?string $state = null): Builder
    {
        $query = Fatwa::query()->whereNull('quality_checked_at');

        match ($issue) {
            'no_text' => $query->whereNull('full_text'),
            'no_category' => $query->whereNull('category_id'),
            'no_date' => $query->whereNull('issued_year'),
            'no_decision' => $query->whereNotNull('full_text')->whereNull('decision'),
            'garbled' => $query->whereKey($this->garbledIds()),
            'duplicates' => $query->whereKey($this->duplicateIds()),
        };

        if ($state) {
            $query->whereHas('authority', fn ($q) => $q->where('state', $state));
        }

        return $query;
    }

    /**
     * OCR text that is mostly not words (stamps, Jawi read as Latin, bad scans).
     * A "word" here is a run of letters, optionally ending in punctuation.
     */
    public static function looksGarbled(?string $text): bool
    {
        $tokens = preg_split('/\s+/u', trim((string) $text), -1, PREG_SPLIT_NO_EMPTY);

        if (count($tokens) < 20) {
            return true;
        }

        $words = count(preg_grep('/^[("“‘\'\[]?\pL[\pL’\'\-]*[)"”’\'.,;:!?\]]*$/u', $tokens));

        return $words / count($tokens) < 0.6;
    }

    /** @return list<int> */
    private function garbledIds(): array
    {
        return $this->idCache['garbled'] ??= Fatwa::where('text_from_ocr', true)
            ->whereNotNull('full_text')
            ->get(['id', 'full_text'])
            ->filter(fn (Fatwa $f) => self::looksGarbled($f->full_text))
            ->pluck('id')->values()->all();
    }

    /** Fatwas of the same agency whose titles match once case and punctuation are ignored. @return list<int> */
    private function duplicateIds(): array
    {
        return $this->idCache['duplicates'] ??= Fatwa::get(['id', 'issuing_agency', 'title'])
            ->groupBy(fn (Fatwa $f) => $f->issuing_agency.'|'.self::titleKey($f->title))
            ->filter(fn ($group) => $group->count() > 1)
            ->flatten()->pluck('id')->values()->all();
    }

    public static function titleKey(?string $title): string
    {
        return mb_strtolower(preg_replace('/[^\pL\pN]+/u', '', (string) $title));
    }
}
