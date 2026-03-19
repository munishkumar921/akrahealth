<?php

namespace App\Services;

use App\Models\LabTestCategory;

class LabTestCategoryService
{
    public function list()
    {
        return LabTestCategory::where('hospital_id', auth()->user()->hospital->id)->orwhereNull('hospital_id')->when(request()->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        })
            ->orderBy('id', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
    }

    public function upsert($data)
    {
        LabTestCategory::updateOrCreate(
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
