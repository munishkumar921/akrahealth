<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait FileTrait
{
    /**
     * uploadFile
     *
     * @param  mixed  $request
     * @return void
     */
    public function uploadFile(Request $request)
    {
        if ($request->file()) {
            $file_name = str_replace(' ', '-', strtolower($request->profile_photo->getClientOriginalName()));

            return '/storage/'.$request->file('profile_photo')->storeAs('/', $file_name, 'public');
        }
    }

    /**
     * secureFileUpload
     *
     * @param  mixed  $request
     * @return array
     */
    public function secureFileUpload(Request $request)
    {
        $response = [];
        if ($request->file()) {
            $file_detail = pathinfo($request->file->getClientOriginalName());
            $response['file_name'] = $file_detail['filename'] ?? $request->file->getClientOriginalName();

            $filename = strtolower(Str::random(25)).'-secure~'.str_replace(' ', '-', strtolower($request->file->getClientOriginalName()));
            $response['file_path'] = 'files/'.$request->file('file')->storeAs('/', $filename, 'custom');
        }

        return $response;
    }
}
