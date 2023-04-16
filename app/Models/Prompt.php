<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prompt_context_id',
        'prompt',
        'prompt_type',
        'flagged',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function promptContext()
    {
        return $this->belongsTo(PromptContext::class);
    }

    public function promptResponses()
    {
        return $this->hasMany(PromptResponse::class);
    }

    public function travelTags()
    {
        return $this->belongsToMany(TravelTag::class);
    }
}
