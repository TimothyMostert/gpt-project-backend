<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'travel_mode_id',
        'origin_location_id',
        'destination_location_id',
        'duration',
    ];

    public function travelMode()
    {
        return $this->belongsTo(TravelMode::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function origin()
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destination()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }
}
