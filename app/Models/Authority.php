<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Authority extends Model
{
    protected $fillable = ['authority_name', 'logo', 'contact_info', 'street', 'city', 'poscode', 'state'];

    public function fatwas()
    {
        return $this->hasMany(Fatwa::class, 'issuing_agency');
    }
}
