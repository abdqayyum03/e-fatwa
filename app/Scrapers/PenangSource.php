<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Negeri Pulau Pinang - "Himpunan Fatwa" page.
 * One HTML table of gazetted fatwas: Bil | Perkara (link) | Tarikh Warta.
 *
 * The document links carry a token that expires within about a day, so they
 * can't be stored: the importer reads each PDF through the fresh link
 * (download_url), and the fatwa points back to this list page instead.
 */
class PenangSource implements FatwaSource
{
    use Http;

    private const LIST_URL = 'https://mufti.penang.gov.my/index.php/awam/fatwa-1/himpunan-fatwa';

    public function key(): string
    {
        return 'penang';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Mufti Pulau Pinang', 'state' => 'Pulau Pinang'];
    }

    public function fetch(): iterable
    {
        $xpath = $this->getHtml(self::LIST_URL);

        foreach ($xpath->query('//table//tr[td]') as $row) {
            $cells = $xpath->query('./td', $row);
            $link = $xpath->query('.//a[contains(@href, "/doclink/")]', $row)->item(0);

            if ($cells->length < 3 || ! $link) {
                continue;
            }

            $href = $link->getAttribute('href');
            // Stable part of the link: /doclink/{slug}/{token} -> slug
            $slug = preg_match('#/doclink/([^/]+)#', $href, $m) ? $m[1] : md5($href);

            yield [
                'title' => self::clean($cells->item(1)->textContent),
                'source_url' => self::LIST_URL.'#'.$slug,
                'pdf_url' => null,
                'download_url' => self::absolute(self::LIST_URL, $href),
                'date_issued' => MalayDate::find(self::clean($cells->item(2)->textContent)) ?? self::clean($cells->item(2)->textContent),
                'gazette_no' => null,
                'status' => 'Diwartakan',
                'category' => null,
            ];
        }
    }
}
