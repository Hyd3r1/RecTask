<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Integrations\OpenMeteo;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __construct(
        private OpenMeteo $openmeteo
    ){}

    public function fetchData(Request $request)
    {
        $request->validate([
            'long' => 'required',
            'lat' => 'required',
            'city' => 'required',
            'date' => 'nullable',
        ]);


        try {
            $data = $this->openmeteo->getWeatherForCity($request->city, $request->lat, $request->long, $request->date);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchDataforChart(Request $request)
    {
        $request->validate([
            'long' => 'required',
            'lat' => 'required',
            'city' => 'required',
            'date' => 'nullable',
        ]);

        try {
            $data = $this->openmeteo->getChartData($request->city, $request->lat, $request->long, $request->date);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
            ], 500);
        }
    }
}
