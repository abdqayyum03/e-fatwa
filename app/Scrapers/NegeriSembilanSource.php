<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Negeri Sembilan - WordPress site with a custom "fatwa" post type,
 * read through its JSON API. Each fatwa is published as web text (no PDF), so the
 * full text comes straight from the post. The WordPress date is only the upload
 * date, so the meeting date is read from the text ("bersidang pada 25 Mac 2013").
 */
class NegeriSembilanSource implements FatwaSource
{
    use Http;

    private const API = 'https://muftins.gov.my/wp-json/wp/v2/';

    private const MONTHS = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];

    public function key(): string
    {
        return 'ns';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Mufti Negeri Sembilan', 'state' => 'Negeri Sembilan'];
    }

    public function fetch(): iterable
    {
        // Category ids -> names (e.g. 26 => "Akidah").
        $categories = collect($this->getJson(self::API.'kategori', ['per_page' => 100])['data'])
            ->mapWithKeys(fn ($c) => [$c['id'] => self::clean($c['name'])]);

        for ($page = 1; ; $page++) {
            $result = $this->getJson(self::API.'fatwa', [
                'per_page' => 50,
                'page' => $page,
                '_fields' => 'date,link,title,content,kategori',
            ]);

            foreach ($result['data'] as $post) {
                $html = $post['content']['rendered'] ?? '';
                $categoryName = $categories->get($post['kategori'][0] ?? null);

                // Gazetted fatwas are posted as an embedded PDF ("warta ...") instead of text;
                // the importer then reads the PDF like the other sources.
                $pdf = preg_match('/href="([^"]+\.pdf)"/i', $html, $m) ? html_entity_decode($m[1]) : null;
                $text = $this->htmlToText($html);
                $isGazette = $pdf && mb_strlen((string) $text) < 200;

                yield [
                    'title' => self::clean(strip_tags($post['title']['rendered'] ?? '')),
                    'source_url' => $post['link'],
                    'pdf_url' => $pdf,
                    'full_text' => $isGazette ? null : $text,
                    'date_issued' => $isGazette ? null : $this->meetingDate($text),
                    'gazette_no' => null,
                    'status' => $isGazette ? 'Diwartakan' : null,
                    // "Perubatan,Perubatan" is a duplicate of "Perubatan" on the site.
                    'category' => $categoryName ? explode(',', $categoryName)[0] : null,
                ];
            }

            if ($page >= $result['total_pages']) {
                return;
            }
        }
    }

    /** Keep paragraph and list breaks, drop the HTML. */
    private function htmlToText(string $html): ?string
    {
        $html = preg_replace(['#<br\s*/?>#i', '#</(p|li|h\d|div|tr)>#i'], ["\n", "\n\n"], $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace(["/[ \t\x{00A0}]+/u", "/\n\s*\n+/u"], [' ', "\n\n"], $text);

        return trim($text) === '' ? null : trim($text);
    }

    /** "bersidang pada 25 Mac 2013" -> "25 Mac 2013"; otherwise the year from "Bil. 4/2016". */
    private function meetingDate(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        $months = implode('|', self::MONTHS);
        // Also the older style "8hb Jun 1998" / "26hb. Disember, 2002".
        if (preg_match('/\b(\d{1,2})(?:hb\.?)?\s+('.$months.'),?\s+((?:19|20)\d{2})\b/iu', $text, $m)) {
            return $m[1].' '.ucfirst(mb_strtolower($m[2])).' '.$m[3];
        }

        return preg_match('/Bil\.?\s*\d+\s*\/\s*((?:19|20)\d{2})/iu', $text, $m) ? $m[1] : null;
    }
}
