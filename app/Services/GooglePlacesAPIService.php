<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class GooglePlacesAPIService
{
    private $client;
    private $apiKey;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://maps.googleapis.com/maps/api/place/',
            'timeout'  => 2.0,
        ]);

        $this->apiKey = env('GOOGLE_PLACES_API_KEY');
    }

    private function handleRequestWithRetry($method, $endpoint, $query, $count = 0)
    {
        try {
            $response = $this->client->$method($endpoint, ['query' => $query]);
        
            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody(), true);
                if (isset($result['status']) && $result['status'] != 'OK') {
                    Log::notice('Status not ok in API request to ' . $endpoint . '. Status: ' . $result['status']);
                    return ['error' => $result['status']];
                }
                Log::info('Successful API request to ' . $endpoint . ' with response: ' . json_encode($result));
                return $result;
            } else {
                Log::error('Error in API request to ' . $endpoint . '. Status code: ' . $response->getStatusCode());
                return ['error' => $response->getStatusCode()];
            }
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::notice('Status not ok in API request to ' . $endpoint . ' (Attempt ' . ($count+1) . '): ' . $e->getMessage());
        
            if ($e->getHandlerContext()['errno'] == 28 && $count < 1) {
                // Retry if cURL error 28 (timeout), but limit retries to prevent infinite loop
                $count++;
                Log::warning('Retrying API request to ' . $endpoint . ' due to cURL error 28. Attempt number: ' . ($count+1));
                return $this->handleRequestWithRetry($method, $endpoint, $query, $count);
            } else {
                return ['error' => $e->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error('Exception on API request to ' . $endpoint . ': ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function findPlaceFromText($locationString, $fields = 'place_id,photo,geometry/location')
    {
        $query = [
            'input' => $locationString,
            'inputtype' => 'textquery',
            'fields' => $fields,
            'key' => $this->apiKey
        ];

        $result = $this->handleRequestWithRetry('get', 'findplacefromtext/json', $query);

        if (isset($result['error'])) {
            Log::error('Error finding places from text: ' . $result['error']);
            return ['error' => $result['error']];
        }

        return $result['candidates'];
    }

    public function getPlaceDetails($placeId, $fields = 'photo')
    {
        $query = [
            'place_id' => $placeId,
            'fields' => $fields,
            'key' => $this->apiKey
        ];

        $result = $this->handleRequestWithRetry('get', 'details/json', $query);

        if (isset($result['error'])) {
            Log::error('Error getting place details: ' . $result['error']);
            return ['error' => $result['error']];
        }

        return $result['result'];
    }

    public function detailsFromLocation($locationString, $fields = 'photo,geometry/location')
    {
        $details = [];

        $places = $this->findPlaceFromText($locationString);

        if (isset($places['error'])) {
            Log::error('Error finding places from text: ' . $places['error']);
            return ['error' => $places['error']];
        }

        foreach ($places as $place) {
            $placeDetails = $this->getPlaceDetails($place['place_id'], $fields);

            if (isset($placeDetails['error'])) {
                Log::error('Error getting place details: ' . $placeDetails['error']);
                return ['error' => $placeDetails['error']];
            }

            Log::info('Successful place details request: ' . json_encode($placeDetails));

            $details[$place['place_id']] = $placeDetails;
        }

        return [
            'details' => $details,
        ];
    }
}
