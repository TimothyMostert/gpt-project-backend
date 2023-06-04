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

    public function placeDetailsForPhotosAndGeometry($locationString, $count = 0)
    {
        $photoReferences = [];
        $geometry = [];

        try {

            $placeSearchResponse = $this->client->get('findplacefromtext/json', [
                'query' => [
                    'input' => $locationString,
                    'inputtype' => 'textquery',
                    'fields' => 'place_id',
                    'key' => $this->apiKey
                ]
            ]);

            if ($placeSearchResponse->getStatusCode() == 200) {
                $places = json_decode($placeSearchResponse->getBody(), true);

                // check if staatus not ok
                if ($places['status'] != 'OK') {
                    // check if "status":"ZERO_RESULTS"
                    if ($places['status'] == 'ZERO_RESULTS') {
                        Log::info('ZERO_RESULTS for ' . $locationString);
                        return [
                            'error' => 'ZERO_RESULTS'
                        ];
                    } else if ($places['status'] == 'OVER_QUERY_LIMIT') {
                        Log::error('OVER_QUERY_LIMIT for ' . $locationString);
                        return [
                            'error' => 'OVER_QUERY_LIMIT'
                        ];
                    } else {
                        Log::error('Error getting place id' . $placeSearchResponse->getStatusCode() . ' ' . $placeSearchResponse->getReasonPhrase());
                        return [
                            'error' => 'Error getting place id' . $placeSearchResponse->getStatusCode() . ' ' . $placeSearchResponse->getReasonPhrase()
                        ];
                    }
                }



                foreach ($places['candidates'] as $place) {
                    $placeDetailsResponse = $this->client->get('details/json', [
                        'query' => [
                            'place_id' => $place['place_id'],
                            'fields' => 'photo,geometry/location',
                            'key' => $this->apiKey
                        ]
                    ]);

                    if ($placeDetailsResponse->getStatusCode() == 200) {
                        $placeDetails = json_decode($placeDetailsResponse->getBody(), true);

                        Log::info(json_encode($placeDetails, true));

                        $geometry = $placeDetails['result']['geometry']['location'];
                        foreach ($placeDetails['result']['photos'] as $photo) {
                            $photoReferences[] = $photo['photo_reference'];
                        }
                    } else {
                        Log::error('Error getting place details' . $placeDetailsResponse->getStatusCode() . ' ' . $placeDetailsResponse->getReasonPhrase());
                        return [
                            'error' => 'Error getting place details' . $placeDetailsResponse->getStatusCode() . ' ' . $placeDetailsResponse->getReasonPhrase()
                        ];
                    }
                }
            } else {
                Log::error('Error getting place id' . $placeSearchResponse->getStatusCode() . ' ' . $placeSearchResponse->getReasonPhrase());
                return [
                    'error' => 'Error getting place id' . $placeSearchResponse->getStatusCode() . ' ' . $placeSearchResponse->getReasonPhrase()
                ];
            }
        } catch (RequestException $e) {
            Log::error('RequestException: ' . $e->getMessage());
            // if cURL error 28 (timeout), retry but add count to prevent infinite loop
            if ($e->getCode() == 28 && $count < 3) {
                return $this->placeDetailsForPhotosAndGeometry($locationString, $count + 1);
            } else {
                return [
                    'error' => 'RequestException: ' . $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage());
            // Handle other possible exceptions here...
            return [
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }

        return [
            'photoReferences' => $photoReferences,
            'geometry' => $geometry
        ];
    }
}
