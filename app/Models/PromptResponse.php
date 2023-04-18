<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt_id',
        'response_type',
        'response',
    ];

    protected $casts = [
        'response' => 'array'
    ];

    public function prompt()
    {
        return $this->belongsTo(Prompt::class);
    }
}
