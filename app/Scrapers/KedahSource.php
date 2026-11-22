<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Negeri Kedah - i-Fatwa (ifatwa.kedah.gov.my).
 * Three pages, each one HTML table: Bil | No. Rujukan | Perkara | Tarikh/Tahun | Muat Turun (PDF).
 *   - Fatwa Yang Diwartakan (gazetted)
 *   - Irsyad Al-Fatwa and Irsyad Al-Hukmi (official rulings, not gazetted)
 */
class KedahSource implements FatwaSource
{
    use Http;

    private const BASE = 'https://ifatwa.kedah.gov.my/index.php/';

    /** page slug => status */
    private const PAGES = [
        'laman-senarai-fatwa' => 'Diwartakan',
        'irsyad-al-fatwa' => 'Tidak Diwartakan',
        'irsyad-al-hukmi' => 'Tidak Diwartakan',
    ];

    public function key(): string
    {
        return 'kedah';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Mufti Kedah', 'state' => 'Kedah'];
    }

    public function fetch(): iterable
    {
        foreach (self::PAGES as $slug => $status) {
            $xpath = $this->getHtml(self::BASE.$slug.'/');

            foreach ($xpath->query('//table//tr[td]') as $row) {
                $cells = $xpath->query('./td', $row);
                $pdf = $xpath->query('.//a[contains(@href, ".pdf")]/@href', $row)->item(0)?->nodeValue;

                if ($cells->length < 4 || ! $pdf) {
                    continue;
                }

                $when = self::clean($cells->item(3)->textContent); // "01-Apr-21" or "2020"

                yield [
                    'title' => self::clean($cells->item(2)->textContent),
                    'source_url' => $pdf,
                    'pdf_url' => $pdf,
                    'date_issued' => MalayDate::find($when) ?? $when,
                    'gazette_no' => null, // read from the PDF text by the importer when printed there
                    'status' => $status,
                    'category' => null,
                ];
            }
        }
    }
}
