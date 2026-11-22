<?php

namespace App\Models;

use App\Support\FatwaDecision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Fatwa extends Model
{
    // Allow mass-assignment for these fields
    protected $fillable = [
        'title',
        'content',
        'source_url',
        'pdf_url',
        'full_text',
        'issuing_agency',
        'category_id',
        'date_issued',
        'gazette_no',
        'status',
        'issued_year',
    ];

    protected function casts(): array
    {
        return [
            'text_from_ocr' => 'boolean',
            'quality_checked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Fatwa $fatwa) {
            // Keep issued_year in sync with the free-text date ("2024", "10 Mac 2025", "2024-01-01").
            if ($fatwa->isDirty('date_issued') || $fatwa->issued_year === null) {
                $fatwa->issued_year = self::yearFrom($fatwa->date_issued);
            }

            // The decision box is picked out of the text; refresh it whenever the text changes.
            // (After changing FatwaDecision itself, run `php artisan fatwa:decisions`.)
            if ($fatwa->isDirty('full_text')) {
                $fatwa->decision = FatwaDecision::extract($fatwa->full_text);
            }
        });
    }

    public static function yearFrom(?string $date): ?int
    {
        return $date && preg_match('/\b(19|20)\d{2}\b/', $date, $m) ? (int) $m[0] : null;
    }

    // Relationship to Authority (using issuing_agency as foreign key)
    public function authority()
    {
        return $this->belongsTo(Authority::class, 'issuing_agency');
    }

    // Relationship to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Keyword search over title and full text. Uses the MySQL FULLTEXT index for
     * normal words, and falls back to LIKE for very short terms the index ignores.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return $query;
        }

        if (mb_strlen($keyword) < 3 || $query->getConnection()->getDriverName() !== 'mysql') {
            return $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$keyword}%")
                ->orWhere('full_text', 'like', "%{$keyword}%"));
        }

        return $query->whereFullText(['title', 'full_text'], $keyword);
    }

    /** Filters shared by the fatwa list, search and comparison pages. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->search($filters['search'] ?? null)
            ->when($filters['year'] ?? null, fn ($q, $year) => $q->where('issued_year', $year))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['agency'] ?? null, fn ($q, $id) => $q->where('issuing_agency', $id))
            ->when($filters['state'] ?? null, fn ($q, $state) => $q->whereHas('authority', fn ($a) => $a->where('state', $state)));
    }
}
