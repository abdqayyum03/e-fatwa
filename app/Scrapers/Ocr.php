<?php

namespace App\Scrapers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * Reads text from scanned fatwas with Tesseract OCR (Malay + English).
 * PDFs are first turned into page images with poppler's pdftoppm.
 */
class Ocr
{
    /** Big scanned books aren't worth minutes of OCR each; read the first pages only. */
    private const MAX_PAGES = 20;

    public static function enabled(): bool
    {
        return (bool) config('services.ocr.enabled');
    }

    /** OCR every page of a PDF already on disk. */
    public function pdf(string $pdfPath): ?string
    {
        $dir = $this->tempDir();

        try {
            $result = Process::timeout(300)->run([
                config('services.ocr.pdftoppm'), '-r', '300', '-gray', '-png',
                '-f', '1', '-l', (string) self::MAX_PAGES, $pdfPath, $dir.'/page',
            ]);

            if (! $result->successful()) {
                throw new \RuntimeException('pdftoppm failed: '.trim($result->errorOutput()));
            }

            $pages = glob($dir.'/page-*.png');
            natsort($pages);

            return $this->read($pages);
        } finally {
            File::deleteDirectory($dir);
        }
    }

    /** Download images (e.g. a post's scanned pages) and OCR them in order. */
    public function images(array $urls): ?string
    {
        $dir = $this->tempDir();

        try {
            $files = [];
            foreach (array_slice($urls, 0, self::MAX_PAGES) as $i => $url) {
                usleep(1_000_000); // polite delay
                $file = sprintf('%s/page-%02d.img', $dir, $i + 1);
                if (ScraperClient::make()->timeout(120)->withOptions(['sink' => $file])->get($url)->successful()) {
                    $files[] = $file;
                }
            }

            return $this->read($files);
        } finally {
            File::deleteDirectory($dir);
        }
    }

    /** @param  list<string>  $files */
    private function read(array $files): ?string
    {
        $pages = [];

        foreach ($files as $file) {
            $result = Process::timeout(180)->run([
                config('services.ocr.tesseract'), $file, '-', '-l', config('services.ocr.languages'),
            ]);

            if (! $result->successful()) {
                throw new \RuntimeException('tesseract failed: '.trim($result->errorOutput()));
            }

            $pages[] = trim($result->output());
        }

        $text = trim(preg_replace(["/[ \t]+/u", "/\n{3,}/u"], [' ', "\n\n"], implode("\n\n", $pages)));

        // A page of noise (stamps, logos) isn't text worth keeping.
        return mb_strlen(preg_replace('/\s+/u', '', $text)) > 200 ? $text : null;
    }

    private function tempDir(): string
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'fatwa-ocr-'.bin2hex(random_bytes(6));
        File::ensureDirectoryExists($dir);

        return $dir;
    }
}
