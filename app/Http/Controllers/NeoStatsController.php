<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NeoStatsController extends Controller
{
    public function fetchNeoStats(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'))->format('Y-m-d');
        $endDate = Carbon::parse($request->input('end_date'))->format('Y-m-d');

        // Fetch data from NASA's API
        $response = Http::get('https://api.nasa.gov/neo/rest/v1/feed', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'api_key' => env('NASA_API_KEY') // Set your NASA API Key in .env
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch data from NASA API'], 500);
        }

        $neoData = $response->json()['near_earth_objects'];

        $stats = $this->calculateStats($neoData);

        return response()->json($stats);
    }

    /**
     * Calculate stats from Neo data.
     *
     * @param array $neoData
     * @return array
     */
    public function calculateStats(array $neoData): array
    {
        $fastestAsteroid = null;
        $closestAsteroid = null;
        $totalSize = 0;
        $asteroidCount = 0;
        $dailyAsteroids = [];

        foreach ($neoData as $date => $asteroids) {
            $dailyAsteroids[$date] = count($asteroids);
            foreach ($asteroids as $asteroid) {
                $asteroidSpeed = $asteroid['close_approach_data'][0]['relative_velocity']['kilometers_per_hour'];
                $asteroidDistance = $asteroid['close_approach_data'][0]['miss_distance']['kilometers'];
                $asteroidSize = ($asteroid['estimated_diameter']['kilometers']['estimated_diameter_min'] +
                    $asteroid['estimated_diameter']['kilometers']['estimated_diameter_max']) / 2;

                $totalSize += $asteroidSize;
                $asteroidCount++;

                if (!$fastestAsteroid || $asteroidSpeed > $fastestAsteroid['speed']) {
                    $fastestAsteroid = [
                        'id' => $asteroid['id'],
                        'name' => $asteroid['name'],
                        'speed' => $asteroidSpeed,
                    ];
                }

                if (!$closestAsteroid || $asteroidDistance < $closestAsteroid['distance']) {
                    $closestAsteroid = [
                        'id' => $asteroid['id'],
                        'name' => $asteroid['name'],
                        'distance' => $asteroidDistance,
                    ];
                }
            }
        }

        return [
            'fastest_asteroid' => $fastestAsteroid,
            'closest_asteroid' => $closestAsteroid,
            'average_size' => $asteroidCount ? $totalSize / $asteroidCount : 0,
            'daily_asteroids' => $dailyAsteroids,
        ];
    }
}
