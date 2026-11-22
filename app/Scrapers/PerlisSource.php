<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Negeri Perlis - "Himpunan Fatwa Negeri Perlis" (Joomla category).
 * Read through the category's RSS feed, which carries each fatwa's full text,
 * 10 items per page (?limitstart=0, 10, 20 ...). The feed's pubDate is only the
 * upload date, so the meeting date is read from the text ("28-29 JULAI 2026").
 */
class PerlisSource implements FatwaSource
{
    use Http;

    private const FEED = 'https://muftiperlis.gov.my/index.php/himpunan-fatwa-negeri?format=feed&type=rss';

    public function key(): string
    {
        return 'perlis';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Jabatan Mufti Negeri Perlis', 'state' => 'Perlis'];
    }

    public function fetch(): iterable
    {
        $seen = [];

        for ($start = 0; $start <= 2000; $start += 10) {
            usleep($this->delayMs * 1000);
            $xml = $this->client()->get(self::FEED.'&limitstart='.$start)->throw()->body();

            $feed = @simplexml_load_string($xml, options: LIBXML_NOCDATA);
            $items = $feed?->channel?->item;

            if (! $items || count($items) === 0) {
                return;
            }

            $new = 0;
            foreach ($items as $item) {
                $link = trim((string) $item->link);

                if ($link === '' || isset($seen[$link])) {
                    continue;
                }
                $seen[$link] = true;
                $new++;

                $text = $this->htmlToText((string) $item->description);

                yield [
                    'title' => self::clean((string) $item->title),
                    'source_url' => $link,
                    'pdf_url' => null,
                    'full_text' => $text,
                    'date_issued' => MalayDate::find($text, 800)
                        ?? (preg_match('/KALI\s+KE-?\d+\s*\/\s*((?:19|20)\d{2})/iu', (string) $text, $m) ? $m[1] : null),
                    'gazette_no' => null,
                    'status' => null,
                    'category' => null,
                ];
            }

            // Past the last page Joomla repeats the final items instead of returning none.
            if ($new === 0) {
                return;
            }
        }
    }

    private function htmlToText(string $html): ?string
    {
        $html = preg_replace(['#<br\s*/?>#i', '#</(p|li|h\d|div|tr)>#i'], ["\n", "\n\n"], $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace(["/[ \t\x{00A0}]+/u", "/\n\s*\n+/u"], [' ', "\n\n"], $text));

        return $text === '' ? null : $text;
    }
}
