<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DrChronoController extends Controller
{
    protected $accessToken;

    public function redirectToDrChrono()
    {
        $query = http_build_query([
            'client_id' => config('services.drchrono.client_id'),
            'redirect_uri' => config('services.drchrono.redirect'),
            'response_type' => 'code',
            'scope' => 'your-scopes',
        ]);

        return redirect('https://drchrono.com/o/authorize/?'.$query);
    }

    public function handleDrChronoCallback(Request $request)
    {
        $response = Http::asForm()->post('https://drchrono.com/o/token/', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.drchrono.client_id'),
            'client_secret' => config('services.drchrono.client_secret'),
            'redirect_uri' => config('services.drchrono.redirect'),
            'code' => $request->code,
        ]);

        $this->accessToken = $response->json()['access_token'];
    }

    public function getPatient($patientId)
    {
        $response = Http::withToken($this->accessToken)
            ->get("https://drchrono.com/api/patients/{$patientId}/");

        return $response->json();
    }
}
