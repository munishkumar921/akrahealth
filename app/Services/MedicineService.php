<?php

namespace App\Services;

use App\Models\Medicine;

class MedicineService
{
    public function list($request)
    {
        $medicines = Medicine::where('hospital_id', auth()->user()->hospital->id)->orwhereNull('hospital_id')->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('strength', 'like', "%{$search}%")
                    ->orWhere('batch_no', 'like', "%{$search}%");
            });
        })
            ->orderBy('id', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();

        return $medicines;
    }

    public function upsert($data)
    {
        if (! isset($data['id']) || empty($data['id'])) {
            $data['id'] = (string) \Illuminate\Support\Str::uuid();
        }
        $data['hospital_id'] = auth()->user()->hospital->id;

        Medicine::updateOrCreate(
            ['id' => $data['id']],
            $data
        );
    }

    public function getFormData()
    {
        $data['dosage_form'] = [
            ['id' => 'tablet', 'name' => 'Tablet'],
            ['id' => 'capsule', 'name' => 'Capsule'],
            ['id' => 'syrup', 'name' => 'Syrup'],
            ['id' => 'injection', 'name' => 'Injection'],
            ['id' => 'ointment', 'name' => 'Ointment'],
            ['id' => 'spray', 'name' => 'Spray'],
            ['id' => 'drop', 'name' => 'Drop'],
            ['id' => 'powder', 'name' => 'Powder'],
            ['id' => 'gel', 'name' => 'Gel'],
        ];

        $data['route'] = [
            ['id' => 'oral', 'name' => 'Oral'],
            ['id' => 'topical', 'name' => 'Topical'],
            ['id' => 'intravenous', 'name' => 'Intravenous'],
            ['id' => 'intramuscular', 'name' => 'Intramuscular'],
            ['id' => 'sublingual', 'name' => 'Sublingual'],
            ['id' => 'nasal', 'name' => 'Nasal'],
            ['id' => 'rectal', 'name' => 'Rectal'],
            ['id' => 'inhalation', 'name' => 'Inhalation'],
        ];

        return $data;
    }
}
