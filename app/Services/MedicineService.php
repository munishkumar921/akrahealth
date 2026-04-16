<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MedicineService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', $request->input('search', ''))),
            'status' => $request->input('status', ''),
            'dosage_form' => trim((string) $request->input('dosage_form', '')),
            'route_name' => trim((string) $request->input('route_name', '')),
        ];

        $medicines = Medicine::where('hospital_id', auth()->user()->hospital->id)
            ->when($filters['keyword'] !== '', function ($query) use ($filters) {
                $keyword = $filters['keyword'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('brand_name', 'like', "%{$keyword}%")
                        ->orWhere('generic_name', 'like', "%{$keyword}%")
                        ->orWhere('strength', 'like', "%{$keyword}%")
                        ->orWhere('batch_no', 'like', "%{$keyword}%")
                        ->orWhere('composition', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->when($filters['dosage_form'] !== '', function ($query) use ($filters) {
                $query->where('dosage_form', $filters['dosage_form']);
            })
            ->when($filters['route_name'] !== '', function ($query) use ($filters) {
                $query->where('route', $filters['route_name']);
            })
            ->orderBy('id', 'desc')
            ->paginate($request->integer('per_page', paginateLimit()))
            ->withQueryString();

        $medicines->getCollection()->transform(function (Medicine $medicine) {
            $medicine->created_label = optional($medicine->created_at)?->format('F d, Y');
            $medicine->status_label = $medicine->is_active ? 'Active' : 'Inactive';
            $medicine->price_label = $medicine->price !== null && $medicine->price !== ''
                ? trim(($medicine->currency ?: 'USD').' '.$medicine->price)
                : 'N/A';

            return $medicine;
        });

        return $medicines;
    }

    public function upsert($data)
    {
        if (! isset($data['id']) || empty($data['id'])) {
            $data['id'] = (string) \Illuminate\Support\Str::uuid();
        }
        $data['hospital_id'] = auth()->user()->hospital->id;
        $data['is_encrypted'] = (bool) ($data['is_encrypted'] ?? false);

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
