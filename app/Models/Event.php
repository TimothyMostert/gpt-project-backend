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
        'title',
        'description',
        'location_id'
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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class)->orderBy('order', 'asc');
    }
}
