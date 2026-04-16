<?php

namespace App\Services;

use App\Models\LabTestCategory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LabTestCategoryService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', $request->input('search', ''))),
            'status' => $request->input('status', ''),
        ];

        $categories = LabTestCategory::where('hospital_id', auth()->user()->hospital->id)
            ->when($filters['keyword'] !== '', function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['keyword']}%")
                        ->orWhere('description', 'like', "%{$filters['keyword']}%");
                });
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->orderBy('id', 'desc')
            ->paginate($request->integer('per_page', paginateLimit()))
            ->withQueryString();

        $categories->getCollection()->transform(function (LabTestCategory $category) {
            $category->created_label = optional($category->created_at)?->format('F d, Y');
            $category->status_label = $category->is_active ? 'Active' : 'Inactive';

            return $category;
        });

        return $categories;
    }

    public function upsert($data)
    {
        LabTestCategory::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
            ],
            [
                'name' => $data['name'],
                'description' => $data['description'],
                'is_active' => $data['is_active'],
                'hospital_id' => auth()->user()->hospital->id,
            ],
            $data
        );
    }
}
