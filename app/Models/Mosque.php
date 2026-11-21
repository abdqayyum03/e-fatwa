<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mosque extends Model
{
    protected $fillable = ['name', 'location', 'contact_info', 'latitude', 'longitude'];
}
