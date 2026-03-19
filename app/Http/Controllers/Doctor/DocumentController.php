<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Encounter;
use App\Models\Image;
use App\Traits\UploadFileTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class DocumentController extends Controller
{
    use UploadFileTrait;

    /**
     * uploadDocument
     *
     * @param  mixed  $request
     * @return void
     */
    public function uploadDocument(Request $request)
    {
        $data = $request->all();
        $encounter = Encounter::where('id', $data['encounter_id'])->first();
        Image::create([
            'encounter_id' => $data['encounter_id'] ?? null,
            'doctor_id' => $encounter->doctor_id,
            'patient_id' => $encounter->patient_id,
            'name' => $data['name'] ?? null,
            'url' => $data['url'] ?? null,
            'type' => $data['type'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return $this->getDocuments($data['encounter_id'], $data['type']);
    }

    /**
     * getDocuments
     *
     * @param  mixed  $encounter_id
     * @return void
     */
    public function getDocuments($encounter_id, $type)
    {
        return Image::where('encounter_id', $encounter_id)
            ->where('type', $type)
            ->get();
    }

    /**
     * delete
     *
     * @param  mixed  $id
     * @return void
     */
    public function delete($id, $type)
    {
        $image = Image::where('id', $id)->first();
        $encounter_id = $image->encounter_id;
        $image->delete();

        return $this->getDocuments($encounter_id, $type);
    }

    /**
     * uploadFile
     *
     * @param  mixed  $request
     * @return void
     */
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240',
            ]);

            $fileUrl = $this->uploadPublic($request->file('file'));

            return response()->json(['url' => 'storage/'.$fileUrl]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => data_get($e->errors(), 'file.0', 'Invalid file upload.'),
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Upload failed. Please use JPG, PNG, WEBP, GIF, PDF, or DOC file.',
            ], 500);
        }
    }
}
