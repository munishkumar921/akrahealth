<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait UploadFileTrait
{
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/jpg',
        'image/heic',
        'image/heif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /**
     * Validate the file against allowed MIME types
     *
     * @throws ValidationException
     */
    protected function validateUpload(UploadedFile $file): void
    {
        $validator = Validator::make(
            ['file' => $file],
            ['file' => 'required|file|mimetypes:'.implode(',', $this->allowedMimeTypes)]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Upload file to public disk
     */
    public function uploadPublic(UploadedFile $file, string $directory = 'uploads')
    {
        $this->validateUpload($file);
        $path = $file->store($directory, 'public');

        return $path;
    }

    /**
     * Upload file to private storage
     */
    public function uploadPrivate(UploadedFile $file, string $directory = 'secure_files')
    {
        $path = $file->store($directory, 'public');

        return $path;
    }

    /**
     * tempUrl
     *
     * @param  mixed  $secureURL
     * @return void
     */
    public function tempUrl($secureURL)
    {
        return '';
    }
}
