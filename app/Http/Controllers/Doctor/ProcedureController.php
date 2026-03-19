<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Encounter;
use App\Models\Procedure;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    /**
     * upsert
     *
     * @param  mixed  $request
     * @return void
     */
    public function upsert(Request $request)
    {
        $data = $request->all();
        $encounter = Encounter::where('id', $data['encounter_id'])->first();
        Procedure::create([
            'encounter_id' => $data['encounter_id'] ?? null,
            'doctor_id' => $encounter->doctor_id,
            'patient_id' => $encounter->patient_id,
            'encounter_provider' => $data['encounter_provider'] ?? null,
            'date' => $data['date'] ?? null,
            'type' => $data['type'] ?? null,
            'cpt' => $data['cpt'] ?? null,
            'description' => $data['description'] ?? null,
            'complications' => $data['complications'] ?? null,
            'ebl' => $data['ebl'] ?? null,
        ]);

        return $this->getProcedures($data['encounter_id']);
    }

    /**
     * getProcedures
     *
     * @param  mixed  $id
     * @return void
     */
    public function getProcedures($id)
    {
        return Procedure::where('encounter_id', $id)->get();
    }

    /**
     * delete
     *
     * @param  mixed  $id
     * @return void
     */
    public function delete($id)
    {
        $procedure = Procedure::where('id', $id)->first();
        $encounter_id = $procedure->encounter_id;
        $procedure->delete();

        return $this->getProcedures($encounter_id);
    }
}
