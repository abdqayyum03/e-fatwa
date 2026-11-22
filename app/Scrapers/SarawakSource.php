<?php

namespace App\Scrapers;

use Illuminate\Support\Str;

/**
 * Jabatan Mufti Negeri Sarawak - "Senarai Fatwa".
 * The list page loads its rows from fatwa_list_ajax/?page=N (5 rows a page):
 * Bil | Tajuk | Kategori | Tahun | Status | Muat turun (PDF).
 *
 * The PDF links are re-encrypted on every request, so they can't be stored:
 * the importer reads each PDF through the fresh link (download_url) and the
 * fatwa points back to the list page instead.
 */
class SarawakSource implements FatwaSource
{
    use Http;

    private const BASE = 'https://muftinegeri.sarawak.gov.my';

    private const LIST_URL = self::BASE.'/web/subpage/fatwa_list/';

    private const AJAX_URL = self::BASE.'/web/subpage/fatwa_list_ajax/';

    public function key(): string
    {
        return 'sarawak';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Mufti Sarawak', 'state' => 'Sarawak'];
    }

    public function fetch(): iterable
    {
        $page = 1;
        $last = 1;

        do {
            $xpath = $this->getHtml(self::AJAX_URL.'?page='.$page);

            if ($page === 1) {
                // "Last" link: javascript:rec_list("/web/subpage/fatwa_list_ajax/?page=37")
                foreach ($xpath->query('//a[@title="Last"]/@href') as $href) {
                    if (preg_match('/page=(\d+)/', $href->value, $m)) {
                        $last = (int) $m[1];
                    }
                }
            }

            foreach ($xpath->query('//div[@id="resp-table-body"]/div[contains(@class, "resp-table-row")]') as $row) {
                $cells = $xpath->query('./div[contains(@class, "table-body-cell")]', $row);
                $link = $xpath->query('.//a[@href]', $row)->item(0);

                if ($cells->length < 5 || ! ($title = self::clean($cells->item(1)->textContent))) {
                    continue;
                }

                $year = self::clean($cells->item(3)->textContent);

                yield [
                    'title' => $title,
                    'source_url' => self::LIST_URL.'#'.Str::slug(Str::limit($title, 150, '')).'-'.$year,
                    'pdf_url' => null,
                    'download_url' => $link?->getAttribute('href'),
                    // The "Tahun" column is when the record was added (a 2020 PKPP fatwa shows 2023),
                    // so the date printed in the PDF wins and the column is only a fallback.
                    'date_issued' => null,
                    'date_fallback' => preg_match('/^\d{4}$/', (string) $year) ? $year : null,
                    'gazette_no' => null,
                    'status' => self::clean($cells->item(4)->textContent),
                    'category' => self::clean($cells->item(2)->textContent),
                ];
            }
        } while (++$page <= $last);
    }
}
