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
        'event_id',
        'order',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'event_id'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
   
}
