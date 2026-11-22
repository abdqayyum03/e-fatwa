<?php

namespace App\Scrapers;

/**
 * Pejabat Mufti Negeri Sabah. Its "Fatwa" page was still empty in Nov 2026
 * ("Rekod sedang dalam kemaskini"), but the fatwa PDFs are already published
 * in the site's WordPress media library, so they are read from the public
 * media API: PDFs whose title contains "fatwa".
 *
 * Titles look like "2017 Fatwa Hukum ...", "Fatwa 1994 Arqam" or
 * "Fatwa Hukum ..."; the year is taken out of the title when it is there.
 */
class SabahSource implements FatwaSource
{
    use Http;

    private const MEDIA_API = 'https://mufti.sabah.gov.my/wp-json/wp/v2/media';

    public function key(): string
    {
        return 'sabah';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Pejabat Mufti Negeri Sabah', 'state' => 'Sabah'];
    }

    public function fetch(): iterable
    {
        $seen = [];
        $found = [];
        $page = 1;

        do {
            $result = $this->getJson(self::MEDIA_API, [
                'mime_type' => 'application/pdf',
                'per_page' => 100,
                'page' => $page,
                '_fields' => 'source_url,title',
            ]);

            foreach ($result['data'] as $item) {
                $url = $item['source_url'] ?? null;
                $raw = self::clean(strip_tags($item['title']['rendered'] ?? ''));

                // Some uploads are titled with a URL (not always their own): use the file's own name then.
                if ($url && $raw && str_starts_with($raw, 'http')) {
                    $raw = str_replace('-', ' ', pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME));
                }

                // Only fatwas, once each; skip DOS-style names like "FATWAU~1" (no usable title).
                if (! $url || ! $raw || stripos($raw, 'fatwa') === false || str_contains($raw, '~') || isset($seen[$url])) {
                    continue;
                }
                $seen[$url] = true;

                [$title, $year] = self::splitTitle($raw);

                // Most fatwas were uploaded twice ("2017-Fatwa-X.pdf" and "Fatwa_X.pdf"):
                // keep one per title, preferring the upload whose name carries the year.
                $key = mb_strtolower(preg_replace('/[^\pL\pN]+/u', '', $title));
                if (! isset($found[$key]) || ($year && ! $found[$key]['date_issued'])) {
                    $found[$key] = [
                        'title' => $title,
                        'source_url' => $url,
                        'pdf_url' => $url,
                        'date_issued' => $year,
                        'gazette_no' => null,
                        'status' => null,
                        'category' => null,
                    ];
                }
            }
        } while (++$page <= $result['total_pages']);

        yield from array_values($found);
    }

    /** "2017 Fatwa Hukum X" / "92017 Fatwa X" / "Fatwa 1994 X" -> ["Hukum X", "2017"] */
    private static function splitTitle(string $raw): array
    {
        $year = null;
        $raw = trim(preg_replace(['/[-\s]converted$/iu', '/\s+/u'], ['', ' '], str_replace('_', ' ', $raw)));

        if (preg_match('/^\d*?((?:19|20)\d{2})\s+Fatwa\s+(.+)$/iu', $raw, $m)
            || preg_match('/^Fatwa\s+((?:19|20)\d{2})\s+(.+)$/iu', $raw, $m)) {
            [$year, $raw] = [$m[1], $m[2]];
        } else {
            $raw = preg_replace('/^Fatwa\s+/iu', '', $raw);
        }

        // Stray quote marks left over from the upload titles.
        $title = trim($raw, " \t\"”“'’‘");
        if (substr_count($title, '“') !== substr_count($title, '”')) {
            $title = str_replace(['“', '”'], '', $title);
        }
        if (str_ends_with($title, ')') && ! str_contains($title, '(')) {
            $title = rtrim($title, ') ');
        }

        // All-caps sentences become title case; short names ("GISBH", "AHMADI") stay as they are.
        $shout = mb_strtoupper($title) === $title && str_word_count($title) >= 3;

        return [$shout ? mb_convert_case($title, MB_CASE_TITLE) : $title, $year];
    }
}
