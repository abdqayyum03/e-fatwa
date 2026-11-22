<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One `fatwa:scrape` run of one source (see the import_runs migration). */
class ImportRun extends Model
{
    protected $fillable = [
        'source', 'status', 'trigger', 'user_id',
        'created_count', 'updated_count', 'with_text', 'failed_count',
        'error', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['queued', 'running'], true);
    }

    /** "12 min 5 s", or null while it hasn't finished. */
    public function duration(): ?string
    {
        if (! $this->started_at || ! $this->finished_at) {
            return null;
        }

        $seconds = (int) $this->started_at->diffInSeconds($this->finished_at);

        return $seconds >= 60 ? intdiv($seconds, 60).' min '.($seconds % 60).' s' : $seconds.' s';
    }
}
