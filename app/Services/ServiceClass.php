<?php

namespace App\Services;

use App\Models\Service;
use App\Traits\UploadFileTrait;
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
    public function list($request)
    {
        $services = Service::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            })
            ->when(
                $request->status,
                fn ($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->date,
                fn ($q) => $q->whereDate('created_at', $request->date)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        // ✅ Ensure banner URL always exists
        $services->getCollection()->transform(function ($s) {
            if ($s->banner) {
                // Already stored full URL (or relative), normalize to Storage URL
                $s->banner = str_starts_with($s->banner, 'http')
                    ? $s->banner
                    : Storage::url($s->banner);
            } else {
                // Default fallback
                $s->banner = asset('images/avatar.webp');
            }

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
        } elseif (request()->has('old_banner')) {
            $data['banner'] = str_replace(asset('storage/'), '', request()->input('old_banner'));
        } else {
            // ✅ store null in DB (but will show default in list)
            $data['banner'] = null;
        }

        return Service::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'name' => $data['name'],
                'description' => $data['description'],
                'category' => $data['category'],
                'is_active' => $data['is_active'],
                'banner' => $data['banner'],
            ]
        );
    }
}
