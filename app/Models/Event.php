<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type_id',
        'itinerary_id',
        'start_time',
        'end_time',
        'order',
    ];

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }
}
