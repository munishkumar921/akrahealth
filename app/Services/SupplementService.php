<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\Supplement;

class SupplementService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return \App\Models\Supplement
     */
    public function upsert($input)
    {
        $hospital = Hospital::where('user_id', auth()->user()->id)->first();
        $hospitalId = $hospital?->id;
        $supplement = Supplement::updateOrCreate(
            ['id' => $input['id'] ?? null],
            [
                'hospital_id' => $hospitalId ?? null,
                'purchase_date' => $input['purchase_date'] ?? null,
                'description' => $input['sup_description'] ?? null,
                'strength' => $input['sup_strength'] ?? null,
                'manufacturer' => $input['sup_manufacturer'] ?? null,
                'expiration' => $input['sup_expiration'] ?? null,
                'cpt' => $input['cpt'] ?? null,
                'charge' => $input['charge'] ?? null,
                'quantity' => $input['quantity'] ?? 0,
                'sup_lot' => $input['sup_lot'] ?? null,
            ]);

        return $supplement;
    }

    /**
     * destroy
     *
     * @param  mixed  $id
     * @return void
     */
    public function destroy(string $id)
    {
        $supplement = Supplement::findOrFail($id);
        $supplement->delete();
    }

    /**
     * getAll
     *
     * @param  mixed  $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll($search = null)
    {
        $query = Supplement::query();

        if ($search) {
            $query->where('description', 'like', "%{$search}%")
                ->orWhere('manufacturer', 'like', "%{$search}%");
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * findById
     *
     * @param  mixed  $id
     * @return \App\Models\Supplement
     */
    public function findById(string $id)
    {
        return Supplement::findOrFail($id);
    }
}
