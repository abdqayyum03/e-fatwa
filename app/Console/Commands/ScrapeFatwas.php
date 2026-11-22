<?php

namespace App\Console\Commands;

use App\Models\ImportRun;
use App\Scrapers\FatwaImporter;
use App\Scrapers\Sources;
use Illuminate\Console\Command;

class ScrapeFatwas extends Command
{
    protected $signature = 'fatwa:scrape
                            {source?* : Which sources to scrape (wp, perak, selangor, ns, kedah, perlis, penang, melaka, kelantan, johor, sabah, sarawak). Default: all}
                            {--limit= : Only import this many fatwas per source (for testing)}
                            {--no-text : Skip downloading PDFs and extracting their text}
                            {--refresh-text : Re-extract text even for fatwas that already have it}
                            {--trigger=cli : Who started the run (cli, admin, schedule); shown in the admin import history}
                            {--run= : Existing import_runs id to report into (used by the admin "Run now" button)}';

    protected $description = 'Import fatwas from the state mufti websites';

    public function handle(): int
    {
        // Some extracted PDF texts are long; give the run more room than the CLI default.
        ini_set('memory_limit', '512M');

        $available = Sources::all();
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
            $run = $this->startRun($key);

            try {
                $stats = $importer->import($available[$key], $limit);
                $rows[] = [$key, $stats['created'], $stats['updated'], $stats['with_text'], $stats['failed']];

                $run->update([
                    'status' => 'done',
                    'created_count' => $stats['created'],
                    'updated_count' => $stats['updated'],
                    'with_text' => $stats['with_text'],
                    'failed_count' => $stats['failed'],
                    'finished_at' => now(),
                ]);
            } catch (\Throwable $e) {
                $this->error("  {$key} stopped: ".$e->getMessage());
                $rows[] = [$key, '-', '-', '-', 'error'];

                $run->update(['status' => 'failed', 'error' => $e->getMessage(), 'finished_at' => now()]);
            }
        }

        $this->newLine();
        $this->table(['Source', 'New', 'Updated', 'With text', 'Failed'], $rows);

        return self::SUCCESS;
    }

    /** The history row for this source: the admin's queued row if given, else a new one. */
    private function startRun(string $source): ImportRun
    {
        $run = ($id = $this->option('run')) ? ImportRun::find($id) : null;

        $run ??= new ImportRun(['source' => $source, 'trigger' => $this->option('trigger') ?: 'cli']);
        $run->fill(['status' => 'running', 'started_at' => now()])->save();

        return $run;
    }
}
