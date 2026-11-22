<?php

namespace App\Scrapers;

use App\Models\Authority;
use App\Models\Category;
use App\Models\Fatwa;
use Illuminate\Support\Facades\Process;

/**
 * Saves records from any FatwaSource. Re-running is safe: fatwas are matched
 * by source_url, so existing ones are updated instead of duplicated.
 */
class FatwaImporter
{
    private const MALAY_MONTHS = 'Januari|Februari|Mac|April|Mei|Jun|Julai|Ogos|September|Oktober|November|Disember';

    /** @var callable|null */
    private $output;

    public function __construct(
        private bool $withText = true,
        private bool $refreshText = false,
    ) {}

    public function onProgress(callable $output): static
    {
        $this->output = $output;

        return $this;
    }

    /** @return array{created:int, updated:int, with_text:int, failed:int} */
    public function import(FatwaSource $source, ?int $limit = null): array
    {
        $authority = Authority::firstOrCreate(
            ['authority_name' => $source->authority()['authority_name']],
            $source->authority(),
        );

        $stats = ['created' => 0, 'updated' => 0, 'with_text' => 0, 'failed' => 0];
        $count = 0;

        foreach ($source->fetch() as $record) {
            if ($limit !== null && $count++ >= $limit) {
                break;
            }

            try {
                $fatwa = Fatwa::firstOrNew(['source_url' => $record['source_url']]);
                $isNew = ! $fatwa->exists;

                $fatwa->fill([
                    'title' => $record['title'],
                    'pdf_url' => $record['pdf_url'] ?? null,
                    'date_issued' => $record['date_issued'] ?? null,
                    'gazette_no' => $record['gazette_no'] ?? null,
                    'status' => $record['status'] ?? null,
                    'issuing_agency' => $authority->id,
                    'category_id' => $this->categoryId($record['category'] ?? null),
                ]);

                if (! empty($record['full_text'])) {
                    // Sites that publish the fatwa as web text give it to us directly.
                    $fatwa->full_text = $record['full_text'];
                } elseif ($this->withText && $fatwa->pdf_url && ($this->refreshText || $fatwa->full_text === null)) {
                    $fatwa->full_text = $this->pdfText($fatwa->pdf_url);
                }

                // Derived from the stored text on every run, so a re-run without
                // re-downloading never puts back the less accurate listing values.
                if ($text = $fatwa->full_text) {
                    // Selangor gives only the web post date, and some sites give none:
                    // use the gazette date printed in the document instead.
                    if (($source->key() === 'selangor' || ! $fatwa->date_issued) && ($date = $this->malayDate($text))) {
                        $fatwa->date_issued = $date;
                    }

                    // Gazette number printed on the PDF, e.g. "P.U. (B) 59", "Sel. P.U. 17", "N.S. P.U. 12".
                    if (! $fatwa->gazette_no && preg_match('/\b((?:Sel\.|Pk\.|N\.\s?S\.)\s*P\.\s?U\.\s*\d+|P\.\s?U\.\s*\([AB]\)\s*\d+)/u', $text, $m)) {
                        $fatwa->gazette_no = preg_replace('/\s+/', ' ', $m[1]);
                    }
                }

                $fatwa->save();

                $stats[$isNew ? 'created' : 'updated']++;
                $stats['with_text'] += $fatwa->full_text ? 1 : 0;
                $this->report(($isNew ? '+ ' : '~ ').$fatwa->title.($fatwa->full_text ? '' : '  [no text]'));
            } catch (\Throwable $e) {
                $stats['failed']++;
                $this->report('! '.($record['title'] ?? '?').' - '.$e->getMessage());
            }
        }

        return $stats;
    }

    /** Skip PDFs bigger than this (some are huge scanned books with no text anyway). */
    private const MAX_PDF_BYTES = 40 * 1024 * 1024;

    /** Download a PDF and convert it to text with poppler's pdftotext. */
    public function pdfText(string $url): ?string
    {
        usleep(1_000_000); // polite delay

        $pdf = tempnam(sys_get_temp_dir(), 'fatwa');

        try {
            // Stream straight to disk ("sink") so large PDFs never sit in PHP memory.
            $response = ScraperClient::make()
                ->timeout(300)
                ->withOptions([
                    'sink' => $pdf,
                    'on_headers' => function ($response) {
                        if ((int) $response->getHeaderLine('Content-Length') > self::MAX_PDF_BYTES) {
                            throw new \RuntimeException('PDF larger than '.(self::MAX_PDF_BYTES >> 20).' MB, skipped');
                        }
                    },
                ])
                ->get($url);

            if (! $response->successful() || filesize($pdf) > self::MAX_PDF_BYTES
                || file_get_contents($pdf, false, null, 0, 4) !== '%PDF') {
                return null;
            }

            $result = Process::timeout(180)->run([config('services.pdftotext.path'), '-enc', 'UTF-8', '-layout', $pdf, '-']);
        } finally {
            @unlink($pdf);
        }

        if (! $result->successful()) {
            throw new \RuntimeException('pdftotext failed: '.trim($result->errorOutput()));
        }

        // Collapse layout whitespace but keep paragraph breaks.
        $text = preg_replace(["/[ \t]+/u", "/\n{3,}/u"], [' ', "\n\n"], $result->output());
        $text = trim($text);

        // Scanned PDFs produce no (or almost no) real text.
        return mb_strlen(preg_replace('/\s+/u', '', $text)) > 50 ? $text : null;
    }

    private function malayDate(string $text): ?string
    {
        $head = mb_substr($text, 0, 600);

        // "13 Februari 2026", and the older "8hb Jun 1998" / "26hb. Disember, 2002".
        return preg_match('/\b(\d{1,2})(?:hb\.?)?\s+('.self::MALAY_MONTHS.'),?\s+((?:19|20)\d{2})\b/u', $head, $m)
            ? "{$m[1]} {$m[2]} {$m[3]}"
            : null;
    }

    /** Different sites name the same topic differently; store one name per topic. */
    private const CATEGORY_ALIASES = [
        'Ibadah' => 'Ibadat',
        'Muamalat/Ekonomi' => 'Muamalat',
    ];

    private function categoryId(?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        $name = self::CATEGORY_ALIASES[$name] ?? $name;

        return Category::firstOrCreate(['category_name' => $name])->id;
    }

    private function report(string $line): void
    {
        if ($this->output) {
            ($this->output)($line);
        }
    }
}
