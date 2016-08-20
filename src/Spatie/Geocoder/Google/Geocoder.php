<?php

namespace Spatie\Geocoder\Google;

use Exception;
use GuzzleHttp\Client;
use Spatie\Geocoder\Geocoder as GeocoderInterface;

class Geocoder implements GeocoderInterface
{
    /** @var client */
    protected $client;

    /** @var string */
    protected $endpoint = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get the coordinates for a query.
     *
     * @param string $query
     *
     * @return array
     *
     * @throws Exception
     */
    public function getCoordinatesForQuery($query)
    {
        if ($query == '') {
            return false;
        }

        $request = $this->client->createRequest('GET', $this->endpoint);
        $requestQuery = $request->getQuery();
        $requestQuery->set('address', $query);

        $response = $this->client->send($request);

        if ($response->getStatusCode() != 200) {
            throw new Exception('could not connect to googleapis.com/maps/api');
        }

        $fullResponse = $response->json();

        if (! count($fullResponse['results'])) {
            return [
                'lat' => 0,
                'lng' => 0,
                'accuracy' => self::RESULT_NOT_FOUND,
                'formatted_address' => self::RESULT_NOT_FOUND,
            ];
        }

        return [
            'lat' => $fullResponse['results'][0]['geometry']['location']['lat'],
            'lng' => $fullResponse['results'][0]['geometry']['location']['lng'],
            'accuracy' => $fullResponse['results'][0]['geometry']['location_type'],
            'formatted_address' => $fullResponse['results'][0]['formatted_address'],
        ];
    }
}
