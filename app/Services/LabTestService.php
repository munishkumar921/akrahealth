<?php

namespace App\Services;

use App\Models\LabTest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LabTestService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', $request->input('search', ''))),
            'status' => $request->input('status', ''),
            'category_id' => trim((string) $request->input('category_id', '')),
            'sample_type' => trim((string) $request->input('sample_type', '')),
        ];

        $tests = LabTest::with('category', 'created_by', 'updated_by')
            ->where('hospital_id', auth()->user()->hospital->id)
            ->when($filters['keyword'] !== '', function ($query) use ($filters) {
                $keyword = $filters['keyword'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('sample_type', 'like', "%{$keyword}%")
                        ->orWhere('report_time', 'like', "%{$keyword}%")
                        ->orWhereHas('category', function ($qq) use ($keyword) {
                            $qq->where('name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->when($filters['category_id'] !== '', function ($query) use ($filters) {
                $query->where('lab_test_category_id', $filters['category_id']);
            })
            ->when($filters['sample_type'] !== '', function ($query) use ($filters) {
                $query->where('sample_type', $filters['sample_type']);
            })
            ->orderBy('id', 'desc')
            ->paginate($request->integer('per_page', paginateLimit()))
            ->withQueryString();

        $tests->getCollection()->transform(function (LabTest $test) {
            $test->category_name = $test->category?->name;
            $test->created_label = optional($test->created_at)?->format('F d, Y');
            $test->status_label = $test->is_active ? 'Active' : 'Inactive';
            $test->fasting_label = $test->fasting_required ? 'Yes' : 'No';
            $test->home_collection_label = $test->is_home_collection_available ? 'Yes' : 'No';
            $test->price_label = $test->final_price !== null && $test->final_price !== ''
                ? trim(($test->currency ?: 'USD').' '.$test->final_price)
                : 'N/A';

            return $test;
        });

        return $tests;
    }

    public function getSampleTypes(): array
    {
        return [
            ['id' => 'blood', 'name' => 'Blood'],
            ['id' => 'urine', 'name' => 'Urine'],
            ['id' => 'stool', 'name' => 'Stool'],
            ['id' => 'saliva', 'name' => 'Saliva'],
            ['id' => 'sputum', 'name' => 'Sputum'],
            ['id' => 'cerebrospinal_fluid', 'name' => 'Cerebrospinal Fluid'],
            ['id' => 'tissue', 'name' => 'Tissue'],
            ['id' => 'swab', 'name' => 'Swab'],
        ];
    }

    public function upsert($data)
    {
        $data['name'] = $data['name'];
        $data['description'] = $data['description'];
        $data['instructions'] = $data['instructions'];
        LabTest::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
            ],
            [
                'hospital_id' => auth()->user()->hospital->id,
                'lab_test_category_id' => $data['lab_test_category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'sample_type' => $data['sample_type'] ?? null,
                'fasting_required' => $data['fasting_required'] ?? null,
                'report_time' => $data['report_time'] ?? null,
                "instructions" => $data['instructions'] ?? null,
                "price" => $data['price'] ?? null,
                "discount" => $data['discount'] ?? null,
                "final_price" => $data['final_price'] ?? null,
                "currency" => $data['currency'] ?? null,
                "is_home_collection_available" => $data['is_home_collection_available'] ?? null,
                "is_active" => $data['is_active'] ?? null,
            ]
        );
    }
}
