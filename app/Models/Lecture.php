<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A kuliah, ceramah, tazkirah or forum, usually at a mosque and given by a preacher. */
class Lecture extends Model
{
    public const TYPES = ['Kuliah', 'Ceramah', 'Tazkirah', 'Forum'];

    protected $fillable = ['title', 'type', 'description', 'starts_at', 'ends_at', 'mosque_id', 'preacher_id', 'venue'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function preacher()
    {
        return $this->belongsTo(Preacher::class);
    }

    /** Events that haven't finished yet. */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('starts_at', '>=', now()->startOfDay())
            ->orWhere('ends_at', '>=', now()))
            ->orderBy('starts_at');
    }

    /** Where it happens: the mosque name, or the free-text venue. */
    public function place(): ?string
    {
        return $this->mosque?->name ?? $this->venue;
    }
}
