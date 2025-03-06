<?php

namespace App\Http\Integrations;

use Cache;
use DateTime;
use Http;
use stdClass;
use Exception;

class OpenMeteo
{
    private static ?OpenMeteo $instance = null;
    private string $baseUrl = "https://api.open-meteo.com/v1/forecast";

    private function __construct(
        private string $apiKey = ""
    ) {}

    public function __clone() {}

    public function __wakeup() {}

    public static function getInstance(): OpenMeteo
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getWeatherForCity(string $city, string $lat, string $long, ?string $date = null): ?stdClass
    {
        $cacheKey = "openmeteo_weather_{$city}_" . ($date ? "date_{$date}" : 'current_' . now()->format('d_m_y'));

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            if ($date) {
                $url = $this->baseUrl . "?latitude={$lat}&longitude={$long}&timezone=Europe%2FWarsaw"
                    . "&daily=precipitation_sum,temperature_2m_max,temperature_2m_min,wind_speed_10m_max"
                    . "&start_date={$date}&end_date={$date}";
            } else {
                $url = $this->baseUrl . "?latitude={$lat}&longitude={$long}&timezone=Europe%2FWarsaw"
                    . "&current=precipitation,rain,temperature_2m,wind_speed_10m";
            }

            $response = Http::get($url);

            if ($response->failed()) {
                throw new Exception("Failed to fetch weather data. Status: " . $response->status());
            }

            $data = $response->json();

            if (!$date && !isset($data['current'])) {
                throw new Exception("Weather data is not available.");
            }

            $weatherData = new stdClass();
            $weatherData->city = $city;
            $weatherData->latitude = $lat;
            $weatherData->longitude = $long;

            if ($date) {
                $weatherData->temperature = $data['daily']['temperature_2m_max'][0] ?? null;
                $weatherData->rain = $data['daily']['precipitation_sum'][0] ?? null;
                $weatherData->windspeed = $data['daily']['wind_speed_10m_max'][0] ?? null;
                $weatherData->date = $date;
            } else {
                $weatherData->temperature = $data['current']['temperature_2m'];
                $weatherData->windspeed = $data['current']['wind_speed_10m'];
                $weatherData->rain = $data['current']['rain'];
                $weatherData->timestamp = $data['current']['time'];
            }

            Cache::put($cacheKey, $weatherData, 60);

            return $weatherData;

        } catch (Exception $e) {
            throw new Exception("Error fetching weather data: " . $e->getMessage());
        }
    }

    public function getChartData(string $city, string $lat, string $long, ?string $date = null, ?int $hourCount = 24): ?stdClass
    {
        $cacheKey = "openmeteo_chart_{$city}_{$hourCount}_" . ($date ? "date_{$date}" : 'current_' . now()->format('d_m_y'));

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $url = $this->baseUrl . "?latitude={$lat}&longitude={$long}&hourly=temperature_2m,wind_speed_10m,precipitation&timezone=Europe%2FWarsaw";

            if ($date) {
                $url .= "&start_date={$date}&end_date={$date}";
            } else {
                $url .= "&start_date=" . now()->format('Y-m-d') . "&end_date=". now()->format('Y-m-d');
            }

            $response = Http::get($url);

            if ($response->failed()) {
                throw new Exception("Failed to fetch data for chart. Status: " . $response->status());
            }

            $data = $response->json();

            if (!isset($data['hourly']['temperature_2m'])) {
                throw new Exception("Temperature data for chart is not available.");
            }

            $chartData = new stdClass();
            $chartData->date = $date ?? now()->format('Y-m-d');
            $chartData->categories = array_map(function($date) {
                $dt = new DateTime($date);
                
                return $dt->format('Y-m-d\TH:i:s.000\Z');
            }, $data['hourly']['time']);
            $chartData->temperatures = $data['hourly']['temperature_2m'];
            $chartData->wind = $data['hourly']['wind_speed_10m'];
            $chartData->rain = $data['hourly']['precipitation'];

            Cache::put($cacheKey, $chartData, 60 * 60 * $hourCount);

            return $chartData;
        } catch (Exception $e) {
            throw new Exception("Error fetching chart data: " . $e->getMessage());
        }
    }
}
