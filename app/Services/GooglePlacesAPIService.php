<?php

namespace App\Services;

use GuzzleHttp\Client;

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

    public function photoReferences($locationString)
    {
        $photoReferences = [];

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

            foreach ($places['candidates'] as $place) {
                $placeDetailsResponse = $this->client->get('details/json', [
                    'query' => [
                        'place_id' => $place['place_id'],
                        'fields' => 'photo',
                        'key' => $this->apiKey
                    ]
                ]);

                error_log(json_encode($placeDetailsResponse->getBody()));

                if ($placeDetailsResponse->getStatusCode() == 200) {
                    $placeDetails = json_decode($placeDetailsResponse->getBody(), true);

                    foreach ($placeDetails['result']['photos'] as $photo) {
                        $photoReferences[] = $photo['photo_reference'];
                    }
                }
            }
        }

        return $photoReferences;
    }
}
