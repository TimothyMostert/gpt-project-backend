<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'activity_type_id',
        'location_event_id',
        'order',
    ];

    public function activityType()
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function locationEvent()
    {
        return $this->belongsTo(LocationEvent::class);
    }
   
}
