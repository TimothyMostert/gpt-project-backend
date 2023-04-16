<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptContext extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'context'
    ];

    protected $casts = [
        'context' => 'array'
    ];

    public function prompts()
    {
        return $this->hasMany(Prompt::class);
    }

}
