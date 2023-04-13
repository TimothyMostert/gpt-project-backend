<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use OpenAI\Laravel\Facades\OpenAI as OpenAI;

class OpenAIController extends Controller
{
    public function prompt(Request $request)
    {
        $request->validate([
            'prompt' => 'required',
        ]);

        $result = OpenAI::completions()->create([
            'model' => 'text-davinci-003',
            'prompt' => $request->prompt,
            'max_tokens' => 2000,
        ]);
        
        return response()->json($result);
    }
}
