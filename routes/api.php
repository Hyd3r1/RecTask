<?php

use App\Http\Controllers\Api\GeolocationController;
use App\Http\Controllers\Api\WeatherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get("/geolocation/fetch/{long}/{lat}", [GeolocationController::class, "fetch"])->name("geolocation.fetchName");
Route::get("/weather/", [WeatherController::class, "fetchData"])->name("weather.fetchData");
Route::get("/weather/chart", [WeatherController::class, "fetchDataforChart"])->name("weather.fetchDataforChart");