<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\Vaccine;
use App\Models\VaccineTemperature;

class VaccineService
{
    /**
     * upsert
     *
     * @param  mixed  $input
     * @return \App\Models\Vaccine
     */
    public function upsert($input)
    {
        $hospital = Hospital::where('user_id', auth()->user()->id)->first();
        $hospitalId = $hospital?->id;

        return Vaccine::updateOrCreate(
            ['id' => $input['id'] ?? null],
            [
                'hospital_id' => $hospitalId ?? null,
                'date_purchase' => $input['date_purchase'] ?? null,
                'immunization' => $input['immunization'] ?? null,
                'brand' => $input['brand'] ?? null,
                'lot' => $input['lot'] ?? null,
                'manufacturer' => $input['manufacturer'] ?? null,
                'expiration' => $input['expiration'] ?? null,
                'cpt' => $input['cpt'] ?? null,
                'code' => $input['code'] ?? null,
                'quantity' => $input['quantity'] ?? 0,
            ]
        );
    }

    public function upsertTemperature($input)
    {
        $hospital = Hospital::where('user_id', auth()->user()->id)->first();
        $hospitalId = $hospital?->id;

        return VaccineTemperature::updateOrCreate(
            ['id' => $input['id'] ?? null],
            [
                'hospital_id' => $hospitalId ?? null,
                'temperature' => $input['temperature'] ?? null,
                'date' => $input['date'] ?? null,
                'time' => $input['time'] ?? null,
                'action' => $input['action'] ?? null,
            ]
        );
    }
}
