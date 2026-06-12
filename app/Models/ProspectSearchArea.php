<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectSearchArea extends Model
{
    protected $fillable = ['lat', 'lng', 'radius_m', 'results_count', 'new_count'];

    protected $casts = [
        'lat'      => 'float',
        'lng'      => 'float',
        'radius_m' => 'integer',
    ];
}
