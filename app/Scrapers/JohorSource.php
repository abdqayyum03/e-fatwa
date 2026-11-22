<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Negeri Johor - e-Fatwa Johor (said.johor.gov.my).
 * The list shows only the latest 100 by default, but filtering by gazette
 * status returns everything: No. | Tajuk | Tahun | Kategori | Status, each
 * linking to paparan_detail_fatwa.php?id=N, whose "Keputusan / Hujah" section
 * holds the full decision text. About 1,600 fatwas, so a full run takes ~30 min.
 */
class JohorSource implements FatwaSource
{
    use Http;

    private const BASE = 'https://said.johor.gov.my/perkhidmatan/';

    public function key(): string
    {
        return 'johor';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Jabatan Mufti Johor', 'state' => 'Johor'];
    }

    public function fetch(): iterable
    {
        foreach (['Diwartakan', 'Tidak Diwartakan'] as $status) {
            $list = $this->getHtml(self::BASE.'paparan_fatwa.php?'.http_build_query(['kategori' => '', 'pewartaan' => $status, 'search_tajuk' => '']));

            foreach ($list->query('//table//tr[td]') as $row) {
                $cells = $list->query('./td', $row);
                $href = $list->query('.//a[contains(@href, "paparan_detail_fatwa.php")]/@href', $row)->item(0)?->nodeValue;

                if ($cells->length < 5 || ! $href || ! preg_match('/id=(\d+)/', $href, $m)) {
                    continue;
                }

                $url = self::BASE.'paparan_detail_fatwa.php?id='.$m[1];
                $year = self::clean($cells->item(2)->textContent);
                $detail = $this->detail($url);

                yield [
                    // The list truncates titles ("... Lihat perincian..."); the detail page has the full one.
                    'title' => $detail['title'] ?? self::clean(preg_replace('/Lihat perincian.*$/iu', '', $cells->item(1)->textContent)),
                    'source_url' => $url,
                    'pdf_url' => $detail['pdf'],
                    'full_text' => $detail['text'],
                    'date_issued' => $detail['date'] ?? (preg_match('/^(19|20)\d{2}$/', (string) $year) ? $year : null),
                    'gazette_no' => null,
                    'status' => $status,
                    'category' => self::clean($cells->item(3)->textContent),
                ];
            }
        }
    }

    /** @return array{title: ?string, text: ?string, date: ?string, pdf: ?string} */
    private function detail(string $url): array
    {
        $xpath = $this->getHtml($url);
        $lines = [];
        foreach ($xpath->query('//body//text()[normalize-space()][not(ancestor::script) and not(ancestor::style)]') as $node) {
            $lines[] = trim(preg_replace('/\s+/u', ' ', $node->nodeValue));
        }
        $text = implode("\n", $lines);

        $between = function (string $from, string $to) use ($text): ?string {
            $start = mb_stripos($text, $from);
            if ($start === false) {
                return null;
            }
            $start += mb_strlen($from);
            $end = mb_stripos($text, $to, $start);
            $value = trim(mb_substr($text, $start, $end === false ? null : $end - $start), " :\n");

            return $value === '' ? null : $value;
        };

        $decision = $between('Keputusan / Hujah', 'Lampiran Hujah Fatwa');
        $gazetteDate = $between('Tarikh Pewartaan', 'Tarikh Keputusan');
        $decisionDate = $between('Tarikh Keputusan', 'Status Penyebaran');
        $pdf = $xpath->query('//a[contains(translate(@href, "PDF", "pdf"), ".pdf")]/@href')->item(0)?->nodeValue;

        return [
            'title' => self::clean($between('Tajuk', 'Status Pewartaan')),
            'text' => $decision ? trim(preg_replace("/\n{3,}/", "\n\n", $decision)) : null,
            'date' => MalayDate::find($gazetteDate) ?? MalayDate::find($decisionDate),
            'pdf' => $pdf ? self::absolute($url, $pdf) : null,
        ];
    }
}
