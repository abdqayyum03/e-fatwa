<?php

namespace App\Jobs;

use App\Models\ImportRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

/**
 * Runs `fatwa:scrape <source>` for an admin's "Run now" click. Imports take
 * minutes (OCR can take an hour), so it runs on the queue worker, not in the request:
 *   php artisan queue:work --timeout=3600
 */
class RunFatwaImport implements ShouldQueue
{
    use Queueable;

    /** One attempt only: a half-finished import is simply run again by the admin. */
    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public int $runId) {}

    public function handle(): void
    {
        $run = ImportRun::find($this->runId);

        if (! $run || $run->status !== 'queued') {
            return;
        }

        Artisan::call('fatwa:scrape', [
            'source' => [$run->source],
            '--run' => $run->id,
            '--trigger' => 'admin',
            '--no-ansi' => true,
        ]);
    }

    public function failed(?\Throwable $e): void
    {
        ImportRun::whereKey($this->runId)->whereIn('status', ['queued', 'running'])->update([
            'status' => 'failed',
            'error' => $e?->getMessage() ?: 'The queue worker stopped the import.',
            'finished_at' => now(),
        ]);
    }
}
