<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prompt_id',
        'title',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function prompt()
    {
        return $this->belongsTo(Prompt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class)->orderBy('order', 'asc');
    }
}
