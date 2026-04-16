<?php

namespace App\Services;

use App\Models\Service;
use App\Traits\UploadFileTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceClass
{
    use UploadFileTrait;

    /*
    * List of service categories
    */
    private $category = [
        ['id' => 'consultation', 'name' => 'Consultation'],
        ['id' => 'lab_test', 'name' => 'Lab test'],
        ['id' => 'pharmacy', 'name' => 'Pharmacy'],
        ['id' => 'home_healthcare', 'name' => 'Home healthcare'],
        ['id' => 'emergency', 'name' => 'Emergency'],
        ['id' => 'others', 'name' => 'Others'],
    ];

    /*
    * Get the list of service categories
    */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * List services with default banner fallback
     */
    public function list(Request $request)
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', '')),
            'category' => trim((string) $request->input('category', '')),
            'status' => trim((string) $request->input('status', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $services = Service::query()
            ->when($filters['keyword'] !== '', function ($query) use ($filters) {
                $query->where(function ($innerQuery) use ($filters) {
                    $innerQuery
                        ->where('name', 'like', "%{$filters['keyword']}%")
                        ->orWhere('description', 'like', "%{$filters['keyword']}%")
                        ->orWhere('category', 'like', "%{$filters['keyword']}%");
                });
            })
            ->when($filters['category'] !== '', fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->when($filters['date_from'] !== '', fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->orderBy(
                $request->input('sort', 'created_at'),
                $request->input('direction', 'desc')
            )
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        $services->getCollection()->transform(function ($s) {
            $s->banner_url = $s->banner
                ? (str_starts_with($s->banner, 'http') ? $s->banner : Storage::url($s->banner))
                : asset('images/avatar.webp');
            $s->status_label = $s->is_active ? 'Active' : 'Inactive';
            $s->category_label = collect($this->category)->firstWhere('id', $s->category)['name'] ?? ucfirst(str_replace('_', ' ', $s->category));
            $s->created_label = optional($s->created_at)?->format('M d, Y h:i A');

            return $s;
        });

        return $services;
    }

    /*
    * Upsert a service record
    */
    public function upsert($data)
    {
        if (request()->hasFile('banner') && request()->file('banner')->isValid()) {
            $file = request()->file('banner');
            $path = $file->store('uploads/services', 'public');
            $data['banner'] = $path; // store relative path
        } elseif (request()->boolean('remove_banner')) {
            $data['banner'] = null;
        } elseif (! empty($data['old_banner'])) {
            $data['banner'] = str_replace(asset('storage/'), '', (string) $data['old_banner']);
        } else {
            unset($data['banner']);
        }

        return Service::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'banner' => $data['banner'] ?? null,
            ]
        );
    }
}
