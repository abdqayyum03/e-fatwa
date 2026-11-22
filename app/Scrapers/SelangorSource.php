<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Negeri Selangor - WordPress site; read through its JSON API.
 * Category "keputusan-fatwa-diwartakan" holds the gazetted fatwas. Each post
 * embeds the official gazette PDF in a viewer ("source":"...pdf").
 */
class SelangorSource implements FatwaSource
{
    use Http;

    private const API = 'https://www.muftiselangor.gov.my/wp-json/wp/v2/';

    public function key(): string
    {
        return 'selangor';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Mufti Selangor', 'state' => 'Selangor'];
    }

    public function fetch(): iterable
    {
        $categoryId = $this->getJson(self::API.'categories', ['slug' => 'keputusan-fatwa-diwartakan'])['data'][0]['id'] ?? null;

        if (! $categoryId) {
            throw new \RuntimeException('Selangor: gazetted-fatwa category not found.');
        }

        for ($page = 1; ; $page++) {
            $result = $this->getJson(self::API.'posts', [
                'categories' => $categoryId,
                'per_page' => 50,
                'page' => $page,
                '_fields' => 'date,link,title,content',
            ]);

            foreach ($result['data'] as $post) {
                $content = $post['content']['rendered'] ?? '';

                yield [
                    'title' => self::clean(strip_tags($post['title']['rendered'] ?? '')),
                    'source_url' => $post['link'],
                    'pdf_url' => $this->pdfFrom($content),
                    // Post date; replaced by the gazette date from the PDF text when found.
                    'date_issued' => $this->malayDate(substr($post['date'], 0, 10)),
                    'gazette_no' => null,
                    'status' => 'Diwartakan',
                    'category' => null,
                ];
            }

            $totalPages = (int) ($result['headers']['X-WP-TotalPages'][0] ?? 1);
            if ($page >= $totalPages) {
                return;
            }
        }
    }

    /** "2026-01-30" -> "30 Januari 2026", matching the other sources' format. */
    private function malayDate(string $ymd): string
    {
        $months = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
        [$y, $m, $d] = array_map('intval', explode('-', $ymd));

        return "{$d} {$months[$m - 1]} {$y}";
    }

    private function pdfFrom(string $html): ?string
    {
        // PDF viewer config: "source":"https:\/\/...\/file.pdf"
        if (preg_match('/"source":"([^"]+\.pdf)"/i', $html, $m)) {
            return stripslashes($m[1]);
        }

        if (preg_match('/href="([^"]+\.pdf)"/i', $html, $m)) {
            return html_entity_decode($m[1]);
        }

        return null;
    }
}
