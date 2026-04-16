<?php

namespace App\Services;

use App\Models\PharmacyOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PharmacyOrderService
{
    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        return PharmacyOrder::with(['patient.user', 'pharmacy', 'items.medicine'])
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('pharmacy', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                            ->orWhere('license_number', 'like', "%{$search}%")
                            ->orWhere('pincode', 'like', "%{$search}%");
                    })->orWhereHas('patient.user', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    })->orWhere('order_number', 'like', "%{$search}%");
                });
            })
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->when(request('payment_status'), fn ($q, $paymentStatus) => $q->where('payment_status', $paymentStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
    }

    /**
     * create
     *
     * @param  mixed  $data
     * @return void
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $notificationService = app(InAppNotificationService::class);
            $data['uuid'] = Str::uuid();
            $order = PharmacyOrder::create($data);

            foreach ($data['order_items'] as $item) {
                $order->items()->create([
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            app(AuditService::class)->logCreate(
                'PharmacyOrder',
                $order->fresh(),
                'Pharmacy order created'
            );

            $order->load(['items.medicine', 'patient.user', 'pharmacy.user', 'doctor.user', 'deliveryPartner']);

            $notificationService->notifyPharmacy(
                $order->pharmacy,
                $notificationService->buildPayload(
                    'New pharmacy order assigned',
                    'A new encounter-related pharmacy order has been assigned to your portal.',
                    'pharmacy_order_assigned',
                    [
                        'recipient_role' => 'Pharmacy',
                        'pharmacy_order_id' => $order->id,
                        'patient_id' => $order->patient_id,
                        'doctor_id' => $order->doctor_id,
                        'appointment_id' => $order->appointment_id,
                        'related_model_type' => PharmacyOrder::class,
                        'related_model_id' => $order->id,
                    ]
                )
            );

            return $order;
        });
    }

    /**
     * get
     *
     * @param  mixed  $id
     * @return void
     */
    public function get($id)
    {
        return PharmacyOrder::with(['patient', 'pharmacy', 'deliveryPartner', 'items.medicine'])->findOrFail($id);
    }

    /**
     * update
     *
     * @param  mixed  $id
     * @param  mixed  $data
     * @return void
     */
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $notificationService = app(InAppNotificationService::class);
            $order = PharmacyOrder::findOrFail($id);
            $oldOrder = clone $order;
            $order->update($data);

            if (! empty($data['order_items'])) {
                $order->items()->delete();
                foreach ($data['order_items'] as $item) {
                    $order->items()->create([
                        'medicine_id' => $item['medicine_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            app(AuditService::class)->logUpdate(
                'PharmacyOrder',
                $oldOrder,
                $order->fresh(),
                'Pharmacy order updated'
            );

            $order->load(['items.medicine', 'patient.user', 'pharmacy.user', 'doctor.user', 'deliveryPartner']);

            $notificationPayload = $notificationService->buildPayload(
                'Pharmacy order updated',
                'A pharmacy order has been updated.',
                'pharmacy_order_updated',
                [
                    'pharmacy_order_id' => $order->id,
                    'patient_id' => $order->patient_id,
                    'doctor_id' => $order->doctor_id,
                    'appointment_id' => $order->appointment_id,
                    'related_model_type' => PharmacyOrder::class,
                    'related_model_id' => $order->id,
                ]
            );

            $notificationService->notifyUser(
                $order->doctor?->user,
                array_merge($notificationPayload, [
                    'recipient_role' => 'Doctor',
                    'message' => 'Pharmacy updated an assigned order.',
                ])
            );

            app(EmailNotificationService::class)->queueDoctorOrderWorkflowEmail(
                $order->doctor?->user,
                'Pharmacy order updated',
                'A pharmacy order for your patient has been updated.',
                [
                    'Patient: '.($order->patient?->user?->name ?? 'N/A'),
                    'Order Number: '.($order->order_number ?? $order->id),
                    'Status: '.($order->status ?? 'N/A'),
                ],
                route('doctor.orders.index')
            );

            $notificationService->notifyPatient(
                $order->patient,
                array_merge($notificationPayload, [
                    'recipient_role' => 'Patient',
                    'message' => 'Your pharmacy order status has been updated.',
                    'action_url' => route('patient.orders'),
                ])
            );

            $notificationService->notifyPharmacy(
                $order->pharmacy,
                array_merge($notificationPayload, [
                    'recipient_role' => 'Pharmacy',
                    'message' => 'A pharmacy order assigned to you was updated.',
                ])
            );

            return $order;
        });
    }

    /**
     * delete
     *
     * @param  mixed  $id
     * @return void
     */
    public function delete($id)
    {
        $order = PharmacyOrder::findOrFail($id);
        $auditModel = clone $order;
        $order->items()->delete();
        $order->delete();

        app(AuditService::class)->logDelete(
            'PharmacyOrder',
            $auditModel,
            'Pharmacy order deleted'
        );

        return ['message' => 'Pharmacy order deleted successfully'];
    }
}
