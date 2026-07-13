<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory);
    }

    public function storePublicUpload(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/\\');
        $targetDirectory = public_path($directory);

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'file';
        $filename = Str::uuid()->toString() . '.' . strtolower($extension);

        $file->move($targetDirectory, $filename);

        return str_replace('\\', '/', $directory . '/' . $filename);
    }
}
