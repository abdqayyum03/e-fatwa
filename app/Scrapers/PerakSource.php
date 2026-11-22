<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Negeri Perak - gazetted fatwas ("Fatwa Yang Diwarta").
 * One page lists every fatwa: No. | Tajuk Warta (link to PDF) | Tarikh | No. Warta
 * Note: Perak's PDFs are scanned images, so they have no extractable text.
 */
class PerakSource implements FatwaSource
{
    use Http;

    private const LIST_URL = 'https://mufti.perak.gov.my/component/content/article/fatwa?catid=2';

    public function key(): string
    {
        return 'perak';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Mufti Perak', 'state' => 'Perak'];
    }

    public function fetch(): iterable
    {
        $xpath = $this->getHtml(self::LIST_URL);

        foreach ($xpath->query('//table//tr[td]') as $row) {
            $cells = $xpath->query('./td', $row);
            $link = $xpath->query('.//a[@href]', $row)->item(0);

            if ($cells->length < 3 || ! $link) {
                continue;
            }

            $pdf = self::absolute(self::LIST_URL, $link->getAttribute('href'));

            yield [
                'title' => self::clean($link->textContent),
                'source_url' => $pdf,
                'pdf_url' => $pdf,
                'date_issued' => self::clean($cells->item(2)->textContent),
                'gazette_no' => self::clean($cells->item(3)?->textContent),
                'status' => 'Diwartakan',
                'category' => null,
            ];
        }
    }
}
