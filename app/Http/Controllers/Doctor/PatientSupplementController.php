<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\PatientSupplement;
use App\Services\PatientSupplementsService;
use Illuminate\Http\Request;

class PatientSupplementController extends Controller
{
    /**
     * upsert
     *
     * @param  mixed  $request
     * @return void
     */
    public function upsert(Request $request, PatientSupplementsService $obj)
    {
        $request->validate([
            'encounter_id' => 'required',
            'supplement' => 'required',
            'route' => 'required',

        ]);
        $data = $obj->store($request->all());

        return $this->getSupplements($data['encounter_id']);

    }

    /**
     * delete
     *
     * @param  mixed  $id
     * @return void
     */
    public function delete($id)
    {
        $supplement = PatientSupplement::where('id', $id)->first();
        $encounter_id = $supplement->encounter_id;
        $supplement->delete();

        return $this->getSupplements($encounter_id);
    }

    /**
     * getSupplements
     *
     * @param  mixed  $encounter_d
     * @return void
     */
    public function getSupplements($encounter_id)
    {
        return PatientSupplement::where('encounter_id', $encounter_id)->get();
    }
}
