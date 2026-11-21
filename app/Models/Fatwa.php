<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Authority;
use App\Models\Category;

class Fatwa extends Model
{
    // Allow mass-assignment for these fields
    protected $fillable = [
        'title',
        'content',
        'issuing_agency',
        'category_id',
        'date_issued',
    ];

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
}
