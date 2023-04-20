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
        'location_event_id',
        'order',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'location_event_id'
    ];

    public function locationEvent()
    {
        return $this->belongsTo(LocationEvent::class);
    }
   
}
