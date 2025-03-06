<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Integrations\LocationIq;
use Cache;
use Illuminate\Http\Request;

class GeolocationController extends Controller
{
    public function __construct(
        private LocationIq $locationIq
    ) {}

    public function fetch(string $long, string $lat)
    {
        try {
            $data = $this->locationIq->fetchData($long, $lat);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
            ], 500);
        }
    }
}
