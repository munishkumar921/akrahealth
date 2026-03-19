<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Encounter;
use App\Models\Referral;
use App\Models\Speciality;
use Illuminate\Http\Request;

class ReferralController extends Controller
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

        $referral = Referral::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'encounter_id' => $data['encounter_id'] ?? null,
                'patient_id' => $encounter->patient_id,
                'doctor_id' => $data['doctor_id'] ?? null,
                'detail' => $data['detail'] ?? null,
                'code' => $data['code'] ?? null,
                'specialty' => $data['specialty'] ?? null,
                'doctor_id' => $data['doctor_id'] ?? null,
                'pending_date' => $data['pending_date'] ?? null,
                'insurance_id' => $data['insurance_id'] ?? null,
                'note' => $data['note'] ?? null,
            ]
        );

        return response()->json($referral);
    }

    /**
     * getReferral
     *
     * @param  mixed  $id
     * @return void
     */
    public function getReferral($id)
    {
        $referral = Referral::where('encounter_id', $id)->first();

        return response()->json($referral);
    }

    /**
     * getDoctorsBySpecialty
     *
     * @param  mixed  $specialty
     * @return void
     */
    public function getDoctorsBySpecialty($specialty)
    {
        $doctors = $doctors = Speciality::with('doctors.user')
            ->where('id', $specialty)
            ->first()
            ->doctors;

        $list = [];
        foreach ($doctors as $doctor) {
            $list[] = ['id' => $doctor->id, 'name' => $doctor->user->name];
        }

        return response()->json($list);
    }
}
