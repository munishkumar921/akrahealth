<?php

namespace App\Services;

use App\Mail\OrderNotificationMail;
use App\Models\Alert;
use App\Models\Cardiopulmonary;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\Lab;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Radiology;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Traits\AlertTrait;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class OrderService
{
    use AlertTrait;
    use \App\Traits\CommonTrait;

    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function upsertOrder($input)
    {

        $data = $input;
        $hospitalId = auth()->user()->doctor->hospital_id;
        $patientId = auth()->user()->doctor->selected_patient_id ?? null;

        $encounter = Encounter::where('id', $data['encounter_id'])->first();
        $order = Order::create([
            'encounter_id' => $data['encounter_id'] ?? null,
            'doctor_id' => $encounter->doctor_id,
            'patient_id' => $encounter->patient_id,
            'lab_id' => $data['encounter_provider'] ?? null,
            'encounter_provider' => $data['encounter_provider'] ?? null,
            'orders_date' => $data['orders_date'] ?? null,
            'insurance_id' => $data['insurance_id'] ?? null,
            'referrals' => $data['referrals'] ?? null,
            'labs' => $data['labs'] ?? null,
            'radiology' => $data['radiology'] ?? null,
            'cp' => $data['cp'] ?? null,
            'referrals_icd' => isset($data['referrals_icd']) && is_array($data['referrals_icd']) ? json_encode($data['referrals_icd']) : ($data['referrals_icd'] ?? null),
            'labs_icd' => isset($data['labs_icd']) && is_array($data['labs_icd']) ? json_encode($data['labs_icd']) : ($data['labs_icd'] ?? null),
            'radiology_icd' => isset($data['radiology_icd']) && is_array($data['radiology_icd']) ? json_encode($data['radiology_icd']) : ($data['radiology_icd'] ?? null),
            'cp_icd' => isset($data['cp_icd']) && is_array($data['cp_icd']) ? json_encode($data['cp_icd']) : ($data['cp_icd'] ?? null),
            'labs_obtained' => $data['labs_obtained'] ?? null,
            'notes' => $data['notes'] ?? null,
            'pending_date' => $data['pending_date'] ?? null,
            'is_completed' => $data['is_completed'] ?? null,
        ]);

        $orders_type_arr = [
            'labs' => ['Laboratory Orders', 'Laboratory results pending '],
            'radiology' => ['Imaging Orders', 'Radiology results pending'],
            'cp' => ['Cardiopulmonary Orders', 'Cardiopulmonary results pending'],
            'referrals' => ['Referrals', 'Referral pending'],
        ];

        foreach ($orders_type_arr as $type_k => $type_v) {
            // Skip if order type is empty or not set
            if (empty($data[$type_k]) || ! is_string($data[$type_k])) {
                continue;
            }

            $alert_subject = $type_v[1] ?? 'Order pending';

            // Add "NEED TO OBTAIN" for future-dated non-referral orders
            if (
                $type_k !== 'orders_referrals' &&
                ! empty($data['orders_pending_date']) &&
                strtotime($data['orders_pending_date']) > time()
            ) {
                $alert_subject .= ' - NEED TO OBTAIN';
            }

            // Process and clean order items
            $orders_arr = array_filter(explode("\n", $data[$type_k]), function ($item) {
                return ! empty(trim($item));
            });

            $orders_new_arr = array_map(function ($item) {
                return trim(preg_replace('/\[[^\]]*\]/', '', $item));
            }, $orders_arr);

            $orders_new_arr = array_filter($orders_new_arr); // Remove any empty strings

            // Get facility name
            $facility_name = 'Unknown Facility';
            if (! empty($data['lab_id'])) {
                $order_lab = Lab::where('id', $data['lab_id'])
                    ->first();
                $facility_name = $order_lab->name ?? 'Unknown Facility';
            }

            // Build description
            $description = ($type_v[0] ?? 'Orders')." sent to {$facility_name}";

            if ($type_k !== 'orders_referrals' && ! empty($orders_new_arr)) {
                $description .= ': '.implode(', ', $orders_new_arr);
            }

            // Prepare alert data
            $orders_alert_data = [
                'alert' => $alert_subject,
                'description' => $description,
                'date_active' => now()->format('Y-m-d H:i:s'),
                'date_complete' => null,
                'why_reason_not_complete' => null,
                'orders_id' => $order->id,
                'patient_id' => $encounter->patient_id,
                'doctor_id' => $encounter->doctor_id,
                'hospital_id' => $hospitalId,
                'send_message' => 'n',
            ];

            try {
                Alert::create($orders_alert_data);
            } catch (\Exception $e) {
                \Log::error("Failed to process {$type_k} alert: ".$e->getMessage());

                continue;
            }
        }

        // Send notification email
        try {
            // Detect provider type based on fields
            $provider = null;
            $email_to = null;
            if ($order->labs) {
                $provider = Lab::find($order->encounter_provider);
                if ($provider) {
                    $email_to = $provider->email;          // Send to Lab / Radiology / CP
                }
            }

            if ($order->radiology) {
                $provider = Radiology::with('address')->find($order->encounter_provider);
                if ($provider && $provider->address) {
                    $email_to = $provider->address->email;  // Send to Lab / Radiology / CP
                }
            }

            if ($order->cp) {
                $provider = Cardiopulmonary::with('address')->find($order->encounter_provider);
                if ($provider && $provider->address) {
                    $email_to = $provider->address->email;  // Send to Lab / Radiology / CP
                }
            }

            // Patient + doctor
            $patient = Patient::with('user')->find($order->patient_id);
            $doctor = Doctor::with('user')->find($order->doctor_id);

            // Skip email if patient or doctor not found or doesn't have user
            if (! $patient || ! $patient->user || ! $doctor || ! $doctor->user) {
                \Log::warning('Order notification skipped: Missing patient or doctor data', [
                    'order_id' => $order->id,
                    'patient_id' => $order->patient_id,
                    'doctor_id' => $order->doctor_id,
                    'has_patient' => (bool) $patient,
                    'has_patient_user' => $patient ? (bool) $patient->user : false,
                    'has_doctor' => (bool) $doctor,
                    'has_doctor_user' => $doctor ? (bool) $doctor->user : false,
                ]);

                return $this->getOrders($data['encounter_id'], $data['type']);
            }

            // Send Email
            if (! empty($email_to)) {
                Mail::to($email_to)->send(
                    new OrderNotificationMail($order, $patient, $doctor, $provider)
                );

            }
            // Send Bell Notification to Lab Role for any order with labs
            if ($order->labs || $order->radiology || $order->cp) {
                $labUsers = User::whereHas('roles', function ($query) {
                    $query->where('name', 'lab');
                })->get();

                if ($labUsers->isNotEmpty()) {
                    Notification::send($labUsers, new OrderCreatedNotification($order, 'lab'));
                } else {
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send order notification email: '.$e->getMessage());
        }

        return $this->getOrders($data['encounter_id'], $data['type'] ?? null);
    }

    /**
     * getOrders
     *
     * @param  mixed  $encounter_id
     * @param  mixed  $field
     * @return void
     */
    public function getOrders($encounter_id, $field)
    {
        return Order::where('encounter_id', $encounter_id)->whereNotNull($field)->get();
    }
}
