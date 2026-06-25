<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExternalDataController extends Controller
{
    public function geocode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ]);

        $limit = $validated['limit'] ?? 1;

        try {
            $response = $this->nominatimRequest($validated['address'], $limit);
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Geocoding service is currently unavailable.',
            ], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'Geocoding service returned an error.',
                'status' => $response->status(),
            ], 502);
        }

        return response()->json([
            'source' => 'Nominatim / OpenStreetMap',
            'address' => $validated['address'],
            'limit' => (int) $limit,
            'results' => $response->json(),
        ]);
    }

    public function weather(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'forecast_days' => ['sometimes', 'integer', 'min:1', 'max:16'],
            'timezone' => ['sometimes', 'string', 'max:64'],
        ]);

        $forecastDays = $validated['forecast_days'] ?? 7;
        $timezone = $validated['timezone'] ?? 'Europe/Belgrade';

        try {
            $response = $this->weatherRequest(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                (int) $forecastDays,
                $timezone,
            );
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Weather service is currently unavailable.',
            ], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'Weather service returned an error.',
                'status' => $response->status(),
            ], 502);
        }

        return response()->json([
            'source' => 'Open-Meteo',
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'forecast_days' => (int) $forecastDays,
            'timezone' => $timezone,
            'weather' => $response->json(),
        ]);
    }

    public function propertyLocation(Property $property): JsonResponse
    {
        $address = $this->propertyAddress($property);

        try {
            $response = $this->nominatimRequest($address, 1);
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Geocoding service is currently unavailable.',
            ], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'Geocoding service returned an error.',
                'status' => $response->status(),
            ], 502);
        }

        $results = $response->json();

        return response()->json([
            'source' => 'Nominatim / OpenStreetMap',
            'property_id' => $property->id,
            'address' => $address,
            'location' => $results[0] ?? null,
        ]);
    }

    public function propertyWeather(Property $property): JsonResponse
    {
        $address = $this->propertyAddress($property);

        try {
            $geocodeResponse = $this->nominatimRequest($address, 1);
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Geocoding service is currently unavailable.',
            ], 502);
        }

        if ($geocodeResponse->failed()) {
            return response()->json([
                'message' => 'Geocoding service returned an error.',
                'status' => $geocodeResponse->status(),
            ], 502);
        }

        $location = $geocodeResponse->json()[0] ?? null;

        if ($location === null) {
            return response()->json([
                'message' => 'Property location could not be found.',
                'property_id' => $property->id,
                'address' => $address,
            ], 404);
        }

        try {
            $weatherResponse = $this->weatherRequest(
                (float) $location['lat'],
                (float) $location['lon'],
                7,
                'Europe/Belgrade',
            );
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Weather service is currently unavailable.',
            ], 502);
        }

        if ($weatherResponse->failed()) {
            return response()->json([
                'message' => 'Weather service returned an error.',
                'status' => $weatherResponse->status(),
            ], 502);
        }

        return response()->json([
            'source' => 'Open-Meteo',
            'property_id' => $property->id,
            'address' => $address,
            'location' => $location,
            'weather' => $weatherResponse->json(),
        ]);
    }

    private function nominatimRequest(string $address, int $limit): \Illuminate\Http\Client\Response
    {
        return Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => 'RealEstateApp/1.0 (local-development)',
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => $limit,
            ]);
    }

    private function weatherRequest(float $latitude, float $longitude, int $forecastDays, string $timezone): \Illuminate\Http\Client\Response
    {
        return Http::timeout(10)
            ->acceptJson()
            ->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'forecast_days' => $forecastDays,
                'timezone' => $timezone,
                'current' => 'temperature_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m',
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,weather_code',
            ]);
    }

    private function propertyAddress(Property $property): string
    {
        return trim("{$property->address}, {$property->city}");
    }
}