<?php

namespace App\Services;

use App\Models\LabTest;

class LabTestService
{
    public function list($request)
    {
        $tests = LabTest::with('category', 'created_by', 'updated_by')
            ->where('hospital_id', auth()->user()->hospital->id)->orwhereNull('hospital_id')
            ->when(request()->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sample_type', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($qq) use ($search) {
                            $qq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();

        return $tests;
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
            ],
            $data
        );
    }
}
