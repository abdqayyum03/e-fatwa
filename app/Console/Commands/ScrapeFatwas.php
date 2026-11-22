<?php

namespace App\Console\Commands;

use App\Scrapers\FatwaImporter;
use App\Scrapers\FatwaSource;
use App\Scrapers\PerakSource;
use App\Scrapers\SelangorSource;
use App\Scrapers\WilayahPersekutuanSource;
use Illuminate\Console\Command;

class ScrapeFatwas extends Command
{
    protected $signature = 'fatwa:scrape
                            {source?* : Which sources to scrape (wp, perak, selangor). Default: all}
                            {--limit= : Only import this many fatwas per source (for testing)}
                            {--no-text : Skip downloading PDFs and extracting their text}
                            {--refresh-text : Re-extract text even for fatwas that already have it}';

    protected $description = 'Import fatwas from the state mufti websites';

    /** @return array<string, FatwaSource> */
    private function sources(): array
    {
        $sources = [new WilayahPersekutuanSource, new PerakSource, new SelangorSource];

        return collect($sources)->keyBy(fn (FatwaSource $s) => $s->key())->all();
    }

    public function handle(): int
    {
        // Some extracted PDF texts are long; give the run more room than the CLI default.
        ini_set('memory_limit', '512M');

        $available = $this->sources();
        $wanted = $this->argument('source') ?: array_keys($available);

        if ($unknown = array_diff($wanted, array_keys($available))) {
            $this->error('Unknown source: '.implode(', ', $unknown).'. Choose from: '.implode(', ', array_keys($available)));

            return self::FAILURE;
        }

        $importer = (new FatwaImporter(
            withText: ! $this->option('no-text'),
            refreshText: (bool) $this->option('refresh-text'),
        ))->onProgress(fn (string $line) => $this->line('  '.$line));

        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $rows = [];

        foreach ($wanted as $key) {
            $this->info("Scraping {$key}...");

            try {
                $stats = $importer->import($available[$key], $limit);
                $rows[] = [$key, $stats['created'], $stats['updated'], $stats['with_text'], $stats['failed']];
            } catch (\Throwable $e) {
                $this->error("  {$key} stopped: ".$e->getMessage());
                $rows[] = [$key, '-', '-', '-', 'error'];
            }
        }

        $this->newLine();
        $this->table(['Source', 'New', 'Updated', 'With text', 'Failed'], $rows);

        return self::SUCCESS;
    }
}
