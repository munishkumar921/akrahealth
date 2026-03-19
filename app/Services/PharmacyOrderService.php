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
            $data['uuid'] = Str::uuid();
            $order = PharmacyOrder::create($data);

            foreach ($data['order_items'] as $item) {
                $order->items()->create([
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $order->load(['items.medicine', 'patient', 'pharmacy', 'deliveryPartner']);
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
            $order = PharmacyOrder::findOrFail($id);
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

            return $order->load(['items.medicine', 'patient', 'pharmacy', 'deliveryPartner']);
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
        $order->items()->delete();
        $order->delete();

        return ['message' => 'Pharmacy order deleted successfully'];
    }
}
