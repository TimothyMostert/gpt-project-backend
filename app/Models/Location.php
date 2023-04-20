<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'address',
        'city',
        'state',
        'country',
        'type',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function travelEvents()
    {
        return $this->hasMany(TravelEvent::class);
    }

    public function locationEvents()
    {
        return $this->hasMany(LocationEvent::class);
    }
}
