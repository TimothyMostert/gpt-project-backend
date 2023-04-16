<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function prompts()
    {
        return $this->belongsToMany(Prompt::class);
    }
}
