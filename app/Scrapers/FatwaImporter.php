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
        // One agency per state; matched by state so renaming an agency never splits its fatwas.
        $authority = Authority::firstOrCreate(
            ['state' => $source->authority()['state']],
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
                    'gazette_no' => $record['gazette_no'] ?? null,
                    'status' => $record['status'] ?? null,
                    'issuing_agency' => $authority->id,
                ]);

                // Sites without categories or dates mustn't blank what is already set: categories
                // come from the admin keyword rules or by hand, dates from the text or by hand.
                if ($categoryId = $this->categoryId($record['category'] ?? null)) {
                    $fatwa->category_id = $categoryId;
                }

                if (! empty($record['date_issued'])) {
                    $fatwa->date_issued = $record['date_issued'];
                }

                if (! empty($record['full_text'])) {
                    // Sites that publish the fatwa as web text give it to us directly.
                    $fatwa->full_text = $record['full_text'];
                    $fatwa->text_from_ocr = false;
                } elseif ($this->withText && ($this->refreshText || $fatwa->full_text === null)) {
                    if ($download = $record['download_url'] ?? $fatwa->pdf_url) {
                        // download_url: a link that only works for a short time (e.g. Penang's
                        // tokened links), used to read the PDF but never stored.
                        $fatwa->full_text = $this->pdfText($download);
                        $fatwa->text_from_ocr = $fatwa->full_text !== null && $this->lastWasOcr;
                    } elseif (! empty($record['image_urls']) && Ocr::enabled()) {
                        // Posts that show the fatwa only as scanned page images (Selangor).
                        $fatwa->full_text = (new Ocr)->images($record['image_urls']);
                        $fatwa->text_from_ocr = $fatwa->full_text !== null;
                    }
                }

                // Derived from the stored text on every run, so a re-run without
                // re-downloading never puts back the less accurate listing values.
                if ($text = $fatwa->full_text) {
                    // Selangor gives only the web post date, and some sites give none or
                    // only a year: use the gazette date printed in the document instead
                    // (for a bare year, only a date in that same year).
                    $listed = (string) $fatwa->date_issued;
                    $yearOnly = preg_match('/^\d{4}$/', $listed) === 1;

                    if (($source->key() === 'selangor' || $listed === '' || $yearOnly)
                        && ($date = MalayDate::find($text, 600))
                        && (! $yearOnly || str_contains($date, $listed))) {
                        $fatwa->date_issued = $date;
                    }

                    // A gazette page header ("WARTA KERAJAAN NEGERI SABAH") means it was gazetted.
                    if (! $fatwa->status && preg_match('/WARTA KERAJAAN/u', mb_substr($text, 0, 300))) {
                        $fatwa->status = 'Diwartakan';
                    }

                    // Gazette number printed on the PDF, e.g. "P.U. (B) 59", "Sel. P.U. 17", "N.S. P.U. 12", "M. P.U. 7".
                    if (! $fatwa->gazette_no && preg_match('/\b((?:Sel\.|Pk\.|N\.\s?S\.|M\.)\s*P\.\s?U\.\s*\d+|P\.\s?U\.\s*\([AB]\)\s*\d+)/u', $text, $m)) {
                        $fatwa->gazette_no = preg_replace('/\s+/', ' ', $m[1]);
                    }
                }

                // date_fallback: a less reliable date, used only when nothing better was found.
                $fatwa->date_issued ??= $record['date_fallback'] ?? null;

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

    /** Whether the last pdfText() result came from OCR rather than the PDF's own text. */
    private bool $lastWasOcr = false;

    /**
     * Download a PDF and convert it to text with poppler's pdftotext. Scanned PDFs
     * (no text layer) are read with OCR instead, when it is enabled.
     */
    public function pdfText(string $url): ?string
    {
        usleep(1_000_000); // polite delay
        $this->lastWasOcr = false;

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

            if (! $result->successful()) {
                throw new \RuntimeException('pdftotext failed: '.trim($result->errorOutput()));
            }

            // Collapse layout whitespace but keep paragraph breaks.
            $text = preg_replace(["/[ \t]+/u", "/\n{3,}/u"], [' ', "\n\n"], $result->output());
            $text = trim($text);

            // Scanned PDFs produce no (or almost no) real text: read the page images instead.
            if (mb_strlen(preg_replace('/\s+/u', '', $text)) > 50) {
                return $text;
            }

            if (! Ocr::enabled()) {
                return null;
            }

            $text = (new Ocr)->pdf($pdf);
            $this->lastWasOcr = $text !== null;

            return $text;
        } finally {
            @unlink($pdf);
        }
    }

    /**
     * Different sites name the same topic differently ("IBADAH", "Ibadah",
     * "Aqidah", "SOSIAL SYARIAH", "Lain-lain"); store one name per topic.
     * Keys are lower-case.
     */
    private const CATEGORY_ALIASES = [
        'ibadah' => 'Ibadat',
        'ibadat' => 'Ibadat',
        'aqidah' => 'Akidah',
        'akidah' => 'Akidah',
        'muamalat' => 'Muamalat',
        'muamalat/ekonomi' => 'Muamalat',
        'syariah' => 'Sosial/Syariah',
        'sosial syariah' => 'Sosial/Syariah',
        'sosial/syariah' => 'Sosial/Syariah',
        'munakahat' => 'Munakahat',
        'perubatan' => 'Perubatan',
        'pelbagai' => 'Pelbagai',
        'lain-lain' => 'Pelbagai',
    ];

    private function categoryId(?string $name): ?int
    {
        // "Sosial / Syariah", "Sosial Syariah." -> "Sosial/Syariah", "Sosial Syariah"
        $name = preg_replace(['/\s+/u', '/\s*\/\s*/u'], [' ', '/'], (string) $name);
        $name = trim($name, " \t\n.,;:-");

        if ($name === '') {
            return null;
        }

        $name = self::CATEGORY_ALIASES[mb_strtolower($name)]
            ?? (mb_strtoupper($name) === $name ? mb_convert_case($name, MB_CASE_TITLE) : $name);

        return Category::firstOrCreate(['category_name' => $name])->id;
    }

    private function report(string $line): void
    {
        if ($this->output) {
            ($this->output)($line);
        }
    }
}
