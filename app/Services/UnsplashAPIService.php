<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class UnsplashAPIService
{
    private $client;
    private $accessKey;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.unsplash.com/',
            'timeout'  => 2.0,
        ]);

        $this->accessKey = env('UNSPLASH_ACCESS_KEY');
    }

    private function handleRequestWithRetry($method, $endpoint, $query, $count = 0)
    {
        try {
            $response = $this->client->$method($endpoint, [
                'query' => array_merge($query, ['client_id' => $this->accessKey]),
                'headers' => [
                    'Accept-Version' => 'v1',
                ],
            ]);

            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody(), true);
                return $result;
            } else {
                Log::error('Error in API request to ' . $endpoint . '. Status code: ' . $response->getStatusCode());
                return ['error' => $response->getStatusCode()];
            }
        } catch (RequestException $e) {
            Log::notice('Connection exception in API request to ' . $endpoint . ' (Attempt ' . ($count+1) . '): ' . $e->getMessage());

            if ($e->getHandlerContext()['errno'] == 28 && $count < 3) {
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

    public function searchPhotosByLocation($locationString, $page = 1, $perPage = 10)
    {
        $query = [
            'query' => $locationString,
            'page' => $page,
            'per_page' => $perPage
        ];

        $result = $this->handleRequestWithRetry('get', 'search/photos', $query);

        if (isset($result['error'])) {
            Log::error('Error searching photos by location: ' . $result['error']);
            return ['error' => $result['error']];
        }

        // Format the results
        $formattedResult = $this->formatPhotoResponse($result['results']);

        return $formattedResult;
    }

    private function formatPhotoResponse($result) {
        $formattedResult = array_map(function ($photo) {
            return [
                'small_url' => $photo['urls']['small'],
                'regular_url' => $photo['urls']['regular'],
                'full_url' => $photo['urls']['full'],
                'name' => $photo['user']['name'],
                'portfolio_url' => $photo['user']['links']['html'],
            ];
        }, $result);

        return $formattedResult;
    }
}
