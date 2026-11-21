<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'preacher_name',
        'certification_details',
        'location',
        'state',
        'authority_id',
    ];

    public function authority()
    {
        return $this->belongsTo(Authority::class);
    }
}
