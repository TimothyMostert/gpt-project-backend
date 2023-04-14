<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\OpenaiAPIService;
use App\Services\PromptFormatService;

class ItineraryController extends Controller
{
    private $openaiAPIService;
    private $promptFormatService;

    public function __construct()
    {
        $this->openaiAPIService = new OpenaiAPIService();
        $this->promptFormatService = new PromptFormatService();
    }

    public function generateBasicItinerary(Request $request)
    {
        $request->validate([
            'prompt' => 'required',
            'tags' => 'required',
        ]);

        $context = $this->promptFormatService->createItineraryContext($request->prompt, $request->tags);

        $rawItinerary = $this->openaiAPIService->contextualPrompt($context);

        $itinerary = $this->promptFormatService->extractJson($rawItinerary->choices[0]->message->content);

        return response()->json([
            'itinerary' => $itinerary,
            'success' => true
        ]);
    }
}
