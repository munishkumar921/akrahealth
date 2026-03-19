<?php

namespace App\Services;

use App\Models\Speciality;
use App\Traits\UploadFileTrait;
use Illuminate\Support\Facades\Storage;

class SpecialityService
{
    use UploadFileTrait;

    public function list($request)
    {

        $specialities = Speciality::where('hospital_id', auth()->user()->hospital->id)->orwhereNull('hospital_id')
            ->when(
                $request->search,
                fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%")
            )
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();

        // Ensure every banner has a full URL or default
        $specialities->getCollection()->transform(function ($s) {
            if ($s->banner) {
                $s->banner = Storage::url($s->banner);
            } else {
                $s->banner = asset('images/avatar.webp'); // fallback avatar
            }

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
