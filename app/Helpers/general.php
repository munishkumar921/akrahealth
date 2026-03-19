<?php

use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

if (! function_exists('paginateLimit')) {
    /*
    * Get the pagination limit from settings or return a default value.
     */
    function paginateLimit($default = 10)
    {
        return 10; // DB::table('settings')->where('key', 'PaginateLimit')->value('value') ?? $default;
    }
}

if (! function_exists('getRoleId')) {
    /*
    * get role id by role name
    */
    function getRoleId($roleName)
    {
        return DB::table('roles')->where('name', $roleName)->value('id') ?? '';
    }
}

if (! function_exists('dateFormat')) {
    /*
    * Format a date to a specified format.
    * Default format: "Feb 02, 2026" (F d, Y format)
    * If the date is null, return an empty string.
    */
    function dateFormat($date, $format = 'F d, Y')
    {
        return $date ? \Carbon\Carbon::parse($date)->format($format) : '';
    }
}

if (! function_exists('hasAccess')) {
    /*
    * Format a date to a specified format.
    * If the date is null, return an empty string.
    */
    function hasAccess($permission)
    {
        if (Auth::check() && Auth::user()->can($permission)) {
            return true;
        } else {
            abort(403, "You do not have permission to $permission.");
        }
    }
}

if (! function_exists('getSingleField')) {

    /**
     * getSingleField
     *
     * @param  mixed  $data
     * @return void
     */
    function getSingleField($data)
    {
        $field = json_decode($data);
        $lang = request()->language ?? 'en';

        return $field->{$lang} ?? null;
    }
}

if (! function_exists('getConfig')) {

    /**
     * getConfig
     *
     * @param  mixed  $key
     * @return void
     */
    function getConfig($key)
    {
        return Setting::where('key', $key)->first()->value ?? null;
    }
}

if (! function_exists('getLatLong')) {

    /**
     * getLatLong
     *
     * @param  mixed  $address
     * @return void
     */
    function getLatLong($address)
    {
        $apiKey = getConfig('GOOGLE_MAPS_API_KEY');
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';

        $response = Http::get($url, [
            'address' => $address,
            'key' => $apiKey,
        ]);

        $data = $response->json();

        if ($data['status'] === 'OK' && isset($data['results'][0]['geometry']['location'])) {
            return [
                'lat' => $data['results'][0]['geometry']['location']['lat'],
                'lng' => $data['results'][0]['geometry']['location']['lng'],
            ];
        }

        return [
            'lat' => null,
            'lng' => null,
        ];
    }
}

if (! function_exists('dataFromJson')) {
    /**
     * Generate a GROUP_CONCAT on a JSON field with language extraction
     *
     * @param  string  $tableDotColumn  e.g. 'specialities.name'
     * @param  string|null  $alias  e.g. 'specialities'
     * @param  string|null  $lang  Optional, will fallback to auth()->user()->language or 'en'
     * @return \Illuminate\Database\Query\Expression
     */
    function dataFromJson($tableDotColumn, $alias = null, $lang = null)
    {
        $language = $lang ?? (auth()->user()->language ?? 'en');
        $alias = $alias ?? 'field';

        $sql = "GROUP_CONCAT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT({$tableDotColumn}, '$.\"{$language}\"')) SEPARATOR ', ') as {$alias}";

        return DB::raw($sql);
    }
}

if (! function_exists('saveNotification')) {

    /**
     * saveNotification
     * type: 'system','appointment','lab_result','prescription','pharmacy_order','delivery','chat','alert'
     * channel: 'in_app','email','sms','push','whatsapp', 'other'
     * status: 'pending','sent','failed','read','clicked'
     */
    function saveNotification($title, $message, $type, $channel)
    {
        Notification::create([
            'uuid' => Str::uuid(),
            'user_id' => auth()->user()->id,
            'created_by' => auth()->user()->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'channel' => $channel,
            'status' => 'pending',
            'sent_at' => now(),
            'is_viewed' => false,
        ]);

        /*
        * test call
        * saveNotification('test', 'this is a test message', 'alert', 'other');
        */
    }
}
