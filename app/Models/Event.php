<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type_id',
        'uuid',
        'itinerary_id',
        'start_time',
        'end_time',
        'order',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function locationEvent()
    {
        return $this->hasOne(LocationEvent::class);
    }

    public function travelEvent()
    {
        return $this->hasOne(TravelEvent::class);
    }
}
