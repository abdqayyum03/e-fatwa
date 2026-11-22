<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Wilayah Persekutuan - https://efatwa.muftiwp.gov.my
 * Server-rendered table, 10 rows per page, paged with ?page=N.
 * Columns: No. | Tajuk | Tahun | Kategori | Status | (download form)
 */
class WilayahPersekutuanSource implements FatwaSource
{
    use Http;

    private const BASE = 'https://efatwa.muftiwp.gov.my/';

    public function key(): string
    {
        return 'wp';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Mufti Wilayah Persekutuan', 'state' => 'Wilayah Persekutuan'];
    }

    public function fetch(): iterable
    {
        for ($page = 1; $page <= 100; $page++) {
            $xpath = $this->getHtml(self::BASE.'?page='.$page);
            $rows = $xpath->query('//table//tbody/tr');

            if ($rows->length === 0) {
                return;
            }

            foreach ($rows as $row) {
                $cells = $xpath->query('./td', $row);
                if ($cells->length < 4) {
                    continue;
                }

                $title = self::clean($cells->item(1)->textContent);
                $pdf = $xpath->query('.//form/@action', $row)->item(0)?->nodeValue;

                if (! $title) {
                    continue;
                }

                yield [
                    'title' => $title,
                    // Many entries have no PDF; the title + year keeps them unique.
                    'source_url' => $pdf ?: self::BASE.'#'.md5($title.$cells->item(2)->textContent),
                    'pdf_url' => $pdf,
                    'date_issued' => self::clean($cells->item(2)->textContent),
                    'category' => self::clean($cells->item(3)->textContent),
                    'status' => $this->status(self::clean($cells->item(4)?->textContent)),
                    'gazette_no' => null,
                ];
            }

            // Stop when there is no "next page" link.
            if ($xpath->query('//ul[contains(@class,"pagination")]//a[@rel="next"]')->length === 0
                && $xpath->query('//ul[contains(@class,"pagination")]//a[contains(@href,"page='.($page + 1).'")]')->length === 0) {
                return;
            }
        }
    }

    private function status(?string $value): ?string
    {
        return match (strtolower((string) $value)) {
            'warta' => 'Diwartakan',
            'tidak warta' => 'Tidak Diwartakan',
            default => null,
        };
    }
}
