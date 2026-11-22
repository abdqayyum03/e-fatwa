<?php

namespace App\Scrapers;

/**
 * Finds the first date in mixed Malay/English text and returns it in one
 * format ("23 Februari 2023"). The mufti sites write dates many ways:
 *   "23hb Februari 2023", "26hb. Disember, 2002", "28-29 JULAI 2026",
 *   "06 Disember 2015", "01-Apr-21", "06/12/2015M", "2024-01-30".
 */
final class MalayDate
{
    public const MONTHS = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];

    private const ENGLISH = ['jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12];

    /** First date in the text (optionally only its first $headChars characters), or null. */
    public static function find(?string $text, ?int $headChars = null): ?string
    {
        if (! $text) {
            return null;
        }

        $text = $headChars ? mb_substr($text, 0, $headChars) : $text;
        $months = implode('|', self::MONTHS);

        // 23hb Februari 2023 / 28-29 JULAI 2026 / 26hb. Disember, 2002
        if (preg_match('/\b(\d{1,2})(?:\s*-\s*\d{1,2})?(?:hb\.?)?\s+('.$months.'),?\s+((?:19|20)\d{2})\b/iu', $text, $m)) {
            return self::format((int) $m[1], self::monthNumber($m[2]), (int) $m[3]);
        }

        // 01-Apr-21 / 1 Apr 2021
        if (preg_match('/\b(\d{1,2})[-\s](Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[-\s]((?:19|20)?\d{2})\b/i', $text, $m)) {
            $year = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];

            return self::format((int) $m[1], self::ENGLISH[strtolower($m[2])], $year);
        }

        // 2024-01-30
        if (preg_match('/\b((?:19|20)\d{2})-(\d{2})-(\d{2})\b/', $text, $m)) {
            return self::format((int) $m[3], (int) $m[2], (int) $m[1]);
        }

        // 06/12/2015 (day/month/year, as used in Malaysia)
        if (preg_match('/\b(\d{1,2})\/(\d{1,2})\/((?:19|20)\d{2})/', $text, $m)) {
            return self::format((int) $m[1], (int) $m[2], (int) $m[3]);
        }

        return null;
    }

    private static function monthNumber(string $name): int
    {
        foreach (self::MONTHS as $i => $month) {
            if (strcasecmp($month, $name) === 0) {
                return $i + 1;
            }
        }

        return 0;
    }

    private static function format(int $day, int $month, int $year): ?string
    {
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        return $day.' '.self::MONTHS[$month - 1].' '.$year;
    }
}
