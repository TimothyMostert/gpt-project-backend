<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_id',
        'location_id'
    ];

    protected $casts = [
        'activities' => 'array'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
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
