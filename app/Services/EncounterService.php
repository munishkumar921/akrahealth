<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Assessment;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\Hospital;
use App\Models\Insurance;
use App\Models\Invoice;
use App\Models\Lab;
use App\Models\LabTestCategory;
use App\Models\PatientIllnessHistory;
use App\Models\Pharmacy;
use App\Models\PhysicalExamination;
use App\Models\Plan;
use App\Models\ReviewOfSystem;
use App\Models\Vital;
use Carbon\Carbon;

class EncounterService
{
    /**
     * upsert
     *
     * @param  mixed  $data
     * @return void
     */
    public function upsert($data)
    {
        $encounter = Encounter::where('id', $data['id'] ?? null)->first();
        if (! $encounter) {
            $encounter = new Encounter;
        }

        if (! isset($data['appointment_id'])) {
            $appointment = Appointment::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
                'appointment_mode' => 'in_person',
                'status' => 'confirmed',
                'appointment_date' => $data['encounter_date_of_service'],
                'appointment_time' => Carbon::parse($data['local_time'])->format('h:i:s A'),
                'payment_status' => 'pending',
                'is_follow_up' => false,
                'recurring_type' => 'none',
            ]);
            $appointment_id = $appointment->id;
        } else {
            $appointment_id = $data['appointment_id'];

            $appointment = Appointment::where('id', $appointment_id)->first();
        }

        $doctor = auth()->user()->doctor;
        $encounter->patient_id = $data['patient_id'];
        $encounter->appointment_id = $appointment_id;
        $encounter->encounter_age = $data['encounter_age'] ?? null;
        $encounter->encounter_type = $data['encounter_type'];
        $encounter->encounter_location = $data['encounter_location'];
        $encounter->encounter_date_of_service = $data['encounter_date_of_service'];
        $encounter->chief_complaint = $data['chief_complaint'];
        $encounter->encounter_signed = 'No';
        $encounter->addendum = 'n';
        $encounter->doctor_id = $data['doctor_id'];
        $encounter->referring_provider = $data['referring_provider'] ?? null;
        $encounter->encounter_condition = $data['encounter_condition'] ?? null;
        $encounter->encounter_condition_work = $data['encounter_condition_work'] ?? null;
        $encounter->encounter_condition_auto = $data['encounter_condition_auto'] ?? null;
        $encounter->encounter_condition_auto_state = $data['encounter_condition_auto_state'] ?? null;
        $encounter->encounter_condition_other = $data['encounter_condition_other'] ?? null;
        $encounter->complexity_of_encounter = $data['complexity_of_encounter'] ?? null;
        $encounter->encounter_role = $data['encounter_role'] ?? null;
        $encounter->hospital_id = $doctor->hospital_id;
        $encounter->save();

        if ($appointment) {

            /* creating invoice with pending status */
            $lastInvoice = Invoice::latest()->first();
            if ($lastInvoice) {
                $invoice_number = $lastInvoice->invoice_number + 1;
            } else {
                $invoice_number = 1000001;
            }

            Invoice::updateOrCreate(
                [
                    'appointment_id' => $encounter->appointment_id,
                ],
                [
                    'invoice_number' => $invoice_number,
                    'patient_id' => $appointment->patient_id,
                    'user_id' => $appointment->created_by,
                    'doctor_id' => $appointment->doctor_id,
                    'hospital_id' => $appointment?->doctor?->hospital?->id,
                    'appointment_id' => $appointment->id,
                    'lab_order_id' => null,
                    'pharmacy_order_id' => null,
                    'subscription_id' => null,
                    'amount' => $appointment->fee_amount ?? 0,
                    'tax_amount' => 0,
                    'discount_amount' => $appointment->discount ?? 0,
                    'total_amount' => $appointment->total_amount ?? 0,
                    'currency' => $appointment->currency ?? 'INR',
                    'status' => 'pending',
                    'razorpay_invoice_id' => null,
                    'razorpay_payment_id' => null,
                    'razorpay_order_id' => null,
                    'razorpay_customer_id' => null,
                    'payment_method' => 'razorpay',
                    'due_date' => now(),
                    'paid_at' => now(),
                    'sent_at' => now(),
                    'viewed_at' => now(),
                    'items' => [],
                    'customer_details' => [],
                    'notes' => '',
                    'terms_conditions' => '',
                    'created_by' => $appointment->created_by,
                    'updated_by' => $appointment->created_by,
                ]
            );
        }

        if ($encounter) {

            if (isset($data['hpi']) && ! empty($data['hpi']) && $data['hpi'] != null) {
                PatientIllnessHistory::updateOrCreate(
                    ['encounter_id' => $encounter->id],
                    [
                        'patient_id' => $encounter->patient_id,
                        'hpi' => $data['hpi'],
                        'date' => now(),
                    ]
                );

                ReviewOfSystem::updateOrCreate(
                    ['encounter_id' => $encounter->id],
                    [
                        'patient_id' => $encounter->patient_id,
                        'encounter_provider' => $encounter->referring_provider,
                        'ros' => $data['ros'] ?? null,
                    ]
                );
            }
            if (! empty($data['weight'])) {
                Vital::updateOrCreate(
                    ['encounter_id' => $encounter->id],
                    [
                        'patient_id' => $encounter->patient_id,
                        'doctor_id' => auth()->user()->doctor?->id,
                        'vital_date' => now(),
                        'age' => $data['age'] ?? null,
                        'passage' => $data['passage'] ?? null,
                        'weight' => $data['weight'] ?? null,
                        'height' => $data['height'] ?? null,
                        'head_circumference' => $data['head_circumference'] ?? null,
                        'bmi' => $data['bmi'] ?? null,
                        'temperature' => $data['temperature'] ?? null,
                        'temperature_method' => $data['temperature_method'] ?? null,
                        'bp_systolic' => $data['bp_systolic'] ?? null,
                        'bp_diastolic' => $data['bp_diastolic'] ?? null,
                        'bp_position' => $data['bp_position'] ?? null,
                        'pulse' => $data['pulse'] ?? null,
                        'respirations' => $data['respirations'] ?? null,
                        'o2_saturation' => $data['o2_saturation'] ?? null,
                        'vitals_other' => $data['vitals_other'] ?? null,
                        'wt_percentile' => $data['wt_percentile'] ?? null,
                        'ht_percentile' => $data['ht_percentile'] ?? null,
                        'hc_percentile' => $data['hc_percentile'] ?? null,
                        'wt_ht_percentile' => $data['wt_ht_percentile'] ?? null,
                        'bmi_percentile' => $data['bmi_percentile'] ?? null,
                    ]
                );
            }

            PhysicalExamination::updateOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'patient_id' => $encounter->patient_id,
                    'doctor_id' => auth()->user()->doctor?->id,
                    'pe' => $data['pe'] ?? null,
                ]
            );

            Assessment::updateOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'patient_id' => $encounter->patient_id,
                    'doctor_id' => auth()->user()->doctor?->id,
                    'assessment_other' => $data['assessment_other'] ?? null,
                    'differential_diagnoses' => $data['differential_diagnoses'] ?? null,
                    'assessment_discussion' => $data['assessment_discussion'] ?? null,
                ]
            );

            Plan::updateOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'patient_id' => $encounter->patient_id,
                    'plan' => $data['plan'] ?? null,
                    'followup' => $data['followup'] ?? null,
                    'duration' => $data['duration'] ?? null,
                ]
            );
            if (isset($encounter) && ! empty($encounter->appointment_id)) {

                $appointment = Appointment::find($encounter->appointment_id);

                if ($appointment) {
                    $appointment->update([
                        'status' => 'completed',
                    ]);
                }
            }
        }

        return $encounter;
    }

    /**
     * getFormData
     *
     * @return void
     */
    public function getFormData($id = null)
    {
        $data['doctors'] = Doctor::with('user:id,name')
            ->select('id', 'user_id')
            ->where('hospital_id', auth()->user()->doctor?->hospital_id)
            ->get();
        $data['encounter_types'] = [
            ['id' => 'medical', 'name' => 'Medical Encounter'],
            ['id' => 'phone', 'name' => 'Phone Encounter'],
            ['id' => 'virtual', 'name' => 'Virtual Encounter'],
            ['id' => 'standardpsych', 'name' => 'Annual Psychiatric Evaluation'],
            ['id' => 'standardpsych1', 'name' => 'Psychiatric Encounter'],
            ['id' => 'clinicalsupport', 'name' => 'Clinical Support Visit'],
            ['id' => 'standardmtm', 'name' => 'Medical Therapy Management Encounter'],
        ];

        $data['locations'] = [
            ['id' => 1, 'name' => 'Pharmacy'],
            ['id' => 3, 'name' => 'School'],
            ['id' => 11, 'name' => 'Office'],
            ['id' => 12, 'name' => 'Home'],
            ['id' => 13, 'name' => 'Assisted Living Facility'],
            ['id' => 14, 'name' => 'Group Home'],
            ['id' => 15, 'name' => 'Mobile Unit'],
            ['id' => 16, 'name' => 'Temporary Lodging'],
            ['id' => 17, 'name' => 'Walk-in Retail Health Clinic'],
            ['id' => 20, 'name' => 'Urgent Care Facility'],
            ['id' => 21, 'name' => 'Inpatient Hospital'],
            ['id' => 22, 'name' => 'Outpatient Hospital'],
            ['id' => 23, 'name' => 'Emergency Room - Hospital'],
            ['id' => 24, 'name' => 'Ambulatory Surgical Center'],
            ['id' => 25, 'name' => 'Birthing Center'],
            ['id' => 26, 'name' => 'Military Treatment Facility'],
            ['id' => 31, 'name' => 'Skilled Nursing Facility'],
            ['id' => 32, 'name' => 'Nursing Facility'],
            ['id' => 41, 'name' => 'Ambulance - Land'],
            ['id' => 42, 'name' => 'Ambulance - Air or Water'],
            ['id' => 49, 'name' => 'Independent Clinic'],
            ['id' => 50, 'name' => 'Federally Qualified Health Center'],
            ['id' => 54, 'name' => 'Intermediate Care Facility'],
            ['id' => 60, 'name' => 'Mass Immunization Center'],
            ['id' => 71, 'name' => 'Public Health Clinic'],
            ['id' => 72, 'name' => 'Rural Health Clinic'],
            ['id' => 99, 'name' => 'Other Place of Service'],
        ];

        $data['provider_roles'] = [
            // ['id' => '', 'name' => 'Choose Provider Role'],
            ['id' => 'Primary Care Provider', 'name' => 'Primary Care Provider'],
            ['id' => 'Consulting Provider', 'name' => 'Consulting Provider'],
            ['id' => 'Referring Provider', 'name' => 'Referring Provider'],
        ];
        $encounter = Encounter::find($id);
        $doctorId = auth()->user()->doctor?->id;

        if (isset($encounter) && ! is_null($encounter->appointment_id)) {

            // Fetch only the specific appointment
            $appointment = Appointment::with('patient.user')
                ->where('id', $encounter->appointment_id)
                ->where('doctor_id', $doctorId)
                ->first();

            // Return as array (ensures same structure as collection)
            $data['appointments'] = $appointment ? [$appointment] : [];
        } else {
            $data['appointments'] = Appointment::with('patient.user')
                ->where('doctor_id', $doctorId)
                ->where('status', 'confirmed')
                ->orderBy('created_at', 'DESC')
                ->get();
        }
        $data['icdCodes'] = $this->getAssessmentCodes($id);

        $data['pharmacies'] = Pharmacy::where('is_active', true)
            ->where('is_verified', true)
            ->select(['id', 'name'])
            ->orderBy('name', 'ASC')
            ->get();

        $data['LabCategories'] = LabTestCategory::select('id', 'name')->where('is_active', true)->orderBy('name', 'ASC')->get();
        $data['insurances'] = Insurance::orderBy('insurance_company', 'ASC')->get();
        $data['labs'] = Lab::select('id', 'name')->where('hospital_id', auth()->user()->doctor?->hospital_id)->where('is_active', true)->orderBy('name', 'ASC')->get();
        $data['pharmacies'] = Pharmacy::select('id', 'name')->where('hospital_id', auth()->user()->doctor?->hospital_id)->where('is_active', true)->orderBy('name', 'ASC')->get();
        $data['radiologies'] = Lab::where('hospital_id', auth()->user()->doctor?->hospital_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('categories', 'like', '%Radiology%');
            })
            ->orderBy('name', 'ASC')
            ->get();
        $data['cardiopulmonary'] = Lab::where('hospital_id', auth()->user()->doctor?->hospital_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('categories', 'like', '%Cardiology%')
                    ->orWhere('categories', 'like', '%Cardiopulmonary%');
            })
            ->orderBy('name', 'ASC')
            ->get();

        $data['imaging'] = Lab::where('hospital_id', auth()->user()->doctor?->hospital_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('categories', 'like', '%Imaging%');
            })
            ->orderBy('name', 'ASC')
            ->get();
        $data['Referral'] = Lab::where('hospital_id', auth()->user()->doctor?->hospital_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('categories', 'like', '%Referral%');
            })
            ->orderBy('name', 'ASC')
            ->get();

        $data['dosage_calculator'] = isset($encounter) ? true : false;
        $data['recent_weight'] = '';
        $weight = Vital::where('patient_id', $encounter->patient_id ?? auth()->user()->doctor?->selected_patient_id)->where('weight', '!=', '')->orderBy('vital_date', 'desc')->first();
        if ($weight) {
            $data['recent_weight'] = $weight->weight ?? '100';
        }
        $hospitalId = auth()->user()->doctor?->hospital_id ?? null;
        $hospital = $hospitalId ? Hospital::find($hospitalId) : null;
        $weight_unit = $hospital ? $hospital->weight_unit : 'lbs';
        $data['weight_unit'] = $weight_unit;

        return $data;
    }

    /**
     * getAssessmentCodes
     *
     * @param  mixed  $id
     * @return void
     */
    public function getAssessmentCodes($id)
    {
        $assessment = Assessment::where('encounter_id', $id)->first();
        $data = [];
        if ($assessment) {
            $data = [
                1 => $assessment->assessment_1,
                2 => $assessment->assessment_2,
                3 => $assessment->assessment_3,
                4 => $assessment->assessment_4,
                5 => $assessment->assessment_5,
                6 => $assessment->assessment_6,
                7 => $assessment->assessment_7,
                8 => $assessment->assessment_8,
                9 => $assessment->assessment_9,
                10 => $assessment->assessment_10,
                11 => $assessment->assessment_11,
                12 => $assessment->assessment_12,
            ];
        }

        return $data;
    }
}
