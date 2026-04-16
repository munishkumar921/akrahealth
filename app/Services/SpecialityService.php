<?php

namespace App\Services;

use App\Models\Speciality;
use App\Traits\UploadFileTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class SpecialityService
{
    use UploadFileTrait;

    public function list(Request $request): LengthAwarePaginator
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', $request->input('search', ''))),
            'status' => $request->input('status', ''),
        ];

        $specialities = Speciality::where('hospital_id', auth()->user()->hospital->id)
            ->when($filters['keyword'] !== '', function ($q) use ($filters) {
                $q->where(function ($inner) use ($filters) {
                    $inner->where('name', 'like', "%{$filters['keyword']}%")
                        ->orWhere('description', 'like', "%{$filters['keyword']}%");
                });
            })
            ->when($filters['status'] !== '', function ($q) use ($filters) {
                $q->where('is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', paginateLimit()))
            ->withQueryString();

        $specialities->getCollection()->transform(function ($s) {
            $s->banner_url = $s->banner
                ? (str_starts_with($s->banner, 'http') ? $s->banner : Storage::url($s->banner))
                : asset('images/avatar.webp');
            $s->created_label = optional($s->created_at)?->format('F d, Y');
            $s->status_label = $s->is_active ? 'Active' : 'Inactive';

            return $s;
        });

        return $specialities;
    }

    /*
    * Upsert a service record
    */
    public function upsert($data)
    {
        if (request()->hasFile('banner') && request()->file('banner')->isValid()) {
            $file = request()->file('banner');
            // Store relative path in DB
            $path = $file->store('uploads/specialities', 'public');
            $data['banner'] = $path;
        } elseif (request()->has('old_banner')) {
            // Keep old relative path
            $data['banner'] = str_replace(asset('storage/'), '', request()->input('old_banner'));
        } else {
            $data['banner'] = null;
        }

        return Speciality::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'name' => $data['name'],
                'hospital_id' => auth()->user()->hospital->id,
                'description' => $data['description'],
                'is_active' => $data['is_active'],
                'banner' => $data['banner'],
            ]
        );
    }
}
