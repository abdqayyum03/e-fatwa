<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Negeri Melaka - "Warta Fatwa", listed by category
 * (/fatwa?cat=aqidah|syariah|muamalat). Each fatwa has its own page showing the
 * gazette as text, plus the gazette PDF; date and gazette no. come from the text.
 */
class MelakaSource implements FatwaSource
{
    use Http;

    private const BASE = 'https://muftimelaka.gov.my';

    /** URL category => category name stored in e-Fatwa */
    private const CATEGORIES = ['aqidah' => 'Akidah', 'syariah' => 'Sosial/Syariah', 'muamalat' => 'Muamalat'];

    public function key(): string
    {
        return 'melaka';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Jabatan Mufti Negeri Melaka', 'state' => 'Melaka'];
    }

    public function fetch(): iterable
    {
        $seen = [];

        foreach (self::CATEGORIES as $cat => $categoryName) {
            $list = $this->getHtml(self::BASE.'/fatwa?cat='.$cat);

            foreach ($list->query('//a[starts-with(@href, "/fatwa/")]/@href') as $hrefNode) {
                $url = self::BASE.strtok($hrefNode->nodeValue, '?#');

                if (isset($seen[$url])) {
                    continue;
                }
                $seen[$url] = true;

                $page = $this->getHtml($url);
                $pdf = $page->query('//a[contains(@href, "/uploads/fatwa/") and contains(@href, ".pdf")]/@href')->item(0)?->nodeValue;
                $title = self::clean($page->query('//h1')->item(0)?->textContent)
                    ?? self::clean(explode('|', (string) $page->query('//title')->item(0)?->textContent)[0]);

                if (! $title) {
                    continue;
                }

                // The page shows the gazette as text (many of the PDFs are scans).
                $lines = [];
                foreach ($page->query('//*[contains(concat(" ", normalize-space(@class), " "), " mufti-fatwa-article ")]//text()[normalize-space()]') as $node) {
                    $lines[] = trim(preg_replace('/\s+/u', ' ', $node->nodeValue));
                }
                $text = trim(implode("\n", $lines));

                yield [
                    'title' => $title,
                    'source_url' => $url,
                    'pdf_url' => $pdf ? self::absolute(self::BASE, $pdf) : null,
                    // Empty text -> the importer falls back to reading the PDF.
                    'full_text' => mb_strlen($text) > 100 ? $text : null,
                    'date_issued' => null, // read from the gazette text ("6hb November 2025")
                    'gazette_no' => null,  // read from the gazette text ("M. P.U. 15")
                    'status' => 'Diwartakan',
                    'category' => $categoryName,
                ];
            }
        }
    }
}
