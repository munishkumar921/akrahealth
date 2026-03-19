<?php

namespace App\Http\Controllers;

use App\Traits\LangHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, LangHelper, ValidatesRequests;

    public $paginate = 10;

    /**
     * striposa
     *
     * @param  mixed  $haystack
     * @param  mixed  $needle
     * @param  mixed  $offset
     * @return void
     */
    protected function striposa($haystack, $needle, $offset = 0)
    {
        if (! is_array($needle)) {
            $needle = [$needle];
        }
        $count = count($needle);
        $i = 0;
        foreach ($needle as $query) {
            if (stripos($haystack, $query, $offset) !== false) {
                $i++;
            }
        }
        if ($i == $count) {
            return true;
        }

        return false;
    }

    /**
     * Get user's location based on IP address.
     * Caches the result for one day, and makes a new request the next day.
     *
     * @return array
     */
    public function getUserLocation()
    {
        $ip = request()->ip();

        $cacheKey = 'user_location_'.$ip; // Unique cache key for each IP

        // Try to retrieve location from cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // If not in cache, make the API request
        $response = Http::get("https://ipwho.is/{$ip}")->json();

        if ($response['error'] ?? false) {
            Log::error('IP Location Error: '.($response['reason'] ?? 'Unknown error'));
            $locationData = [
                'country_code' => 'IN',
                'currency' => 'INR', // Default currency on error
                'ip' => $ip,
            ];
            // Cache error responses for a shorter period to avoid repeated errors
            Cache::put($cacheKey, $locationData, now()->addMinutes(15));

            return $locationData;
        }

        $currency = 'INR'; // Default currency
        if (isset($response['country_code'])) {
            // Your custom logic for currency based on country code
            $currency = ($response['country_code'] === 'IN') ? 'INR' : ($response['currency']['code'] ?? 'USD');
            // ipwho.is provides currency.code in a nested array for success responses
            // Example structure: "currency": {"code": "INR", "name": "Indian Rupee", "symbol": "₹"}
        }

        $locationData = [
            'country' => $response['country'] ?? 'Unknown',
            'country_code' => $response['country_code'] ?? 'US',
            'region' => $response['regionName'] ?? ($response['region'] ?? 'Unknown'), // ipwho.is uses regionName
            'lat' => $response['latitude'] ?? ($response['lat'] ?? 0), // ipwho.is uses latitude
            'lon' => $response['longitude'] ?? ($response['lon'] ?? 0), // ipwho.is uses longitude
            'currency' => $currency,
            'currency_symbol' => $response['currency']['symbol'] ?? '₹', // Default symbol if not provided
            'currency_code' => $response['currency']['code'] ?? 'INR', // Default code if not provided
            'time_zone' => $response['timezone'] ?? 'Asia/Kolkata', // Default timezone if not provided
            'language' => $response['language'] ?? 'en', // Default language if not provided
            'continent' => $response['continent'] ?? 'Asia', // Default continent if not provided
            'continent_code' => $response['continent_code'] ?? 'AS', // Default continent code if not provided
            'ip' => $ip,
        ];

        // Store the successful location data in cache for 24 hours (1440 minutes)
        // This ensures the API is only hit once per day for a given IP
        Cache::put($cacheKey, $locationData, now()->addDays(1));

        return $locationData;
    }
}
