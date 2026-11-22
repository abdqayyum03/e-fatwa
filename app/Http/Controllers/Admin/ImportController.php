<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunFatwaImport;
use App\Models\ImportRun;
use App\Scrapers\Sources;
use Illuminate\Support\Facades\DB;

/** /admin/import: run one source's import from the browser, and the history of runs. */
class ImportController extends Controller
{
    public function index()
    {
        $runs = ImportRun::with('user')->latest('id')->paginate(25);
        $latest = ImportRun::whereIn('id', ImportRun::selectRaw('max(id)')->groupBy('source'))->get()->keyBy('source');

        // A queued run that nobody picked up for a while means no queue worker is running.
        $stuck = ImportRun::where('status', 'queued')->where('created_at', '<', now()->subMinute())->exists();

        return view('admin.imports', [
            'sources' => Sources::states(),
            'latest' => $latest,
            'runs' => $runs,
            'active' => ImportRun::whereIn('status', ['queued', 'running'])->exists(),
            'stuck' => $stuck,
        ]);
    }

    public function store(string $source)
    {
        abort_unless(array_key_exists($source, Sources::all()), 404);

        $run = DB::transaction(function () use ($source) {
            if (ImportRun::where('source', $source)->whereIn('status', ['queued', 'running'])->lockForUpdate()->exists()) {
                return null;
            }

            return ImportRun::create(['source' => $source, 'status' => 'queued', 'trigger' => 'admin', 'user_id' => auth()->id()]);
        });

        if (! $run) {
            return back()->with('error', __('Import untuk sumber ini sedang berjalan.'));
        }

        RunFatwaImport::dispatch($run->id);

        return back()->with('success', __('Import :source dimasukkan ke dalam baris gilir.', ['source' => $source]));
    }
}
