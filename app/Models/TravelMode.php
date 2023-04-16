<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelMode extends Model
{
    use HasFactory;

    protected $filable = [
        'name',
    ];

    public function travelEvents()
    {
        return $this->hasMany(TravelEvent::class);
    }
}
