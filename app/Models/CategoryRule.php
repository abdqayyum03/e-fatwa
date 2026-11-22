<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** "If the title contains one of these keywords, the fatwa belongs in this category." */
class CategoryRule extends Model
{
    protected $fillable = ['category_id', 'keywords', 'priority'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** @return list<string> lower-case keywords */
    public function keywordList(): array
    {
        return array_values(array_filter(array_map(
            fn ($k) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', $k))),
            explode(',', $this->keywords),
        ), fn ($k) => $k !== ''));
    }

    /** Whole-word, any-case match: "solat" matches "Hukum Solat Jumaat" but not "solatnya". */
    public function matches(string $title): bool
    {
        foreach ($this->keywordList() as $keyword) {
            if (preg_match('/(?<![\pL\pN])'.preg_quote($keyword, '/').'(?![\pL\pN])/iu', $title)) {
                return true;
            }
        }

        return false;
    }
}
