<?php

namespace App\Http\Integrations;

use Cache;
use Http;
use Exception;

class LocationIq
{
    private static ?LocationIq $instance = null;

    public function __construct() {}

    public function __clone() {}

    public function __wakeup() {}

    public static function getInstance(): LocationIq
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function fetchData(string $long, string $lat): \stdClass
    {
        if (!Cache::has("locationiq_data_{$long}_{$lat}")) {
            try {
                $response = Http::get("https://us1.locationiq.com/v1/reverse?key=" . config('api.geolocation.locationiq') . "&lat={$lat}&lon={$long}&format=json&");

                if ($response->failed()) {
                    throw new Exception('API request failed with status: ' . $response->status());
                }

                $data = $response->json();

                if (!isset($data['address'])) {
                    throw new Exception('Invalid response structure from API');
                }

                $object = new \stdClass();
                $object->lat = $lat;
                $object->long = $long;
                $object->city = $data["address"]["city"] ?? 'N/A';
                $object->state = $data["address"]["state"] ?? 'N/A';
                $object->country = $data["address"]["country"] ?? 'N/A';

                Cache::put("locationiq_data_{$long}_{$lat}", $object, 60);
            } catch (Exception $e) {
                throw new Exception('Error fetching location data: ' . $e->getMessage());
            }
        }

        return Cache::get("locationiq_data_{$long}_{$lat}");
    }
}
