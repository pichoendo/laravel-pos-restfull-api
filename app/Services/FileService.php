<?php

namespace App\Services;

use App\Models\ImageFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    protected $disk = 'public';

    public function upload(UploadedFile $file, $folder = 'images')
    {

        // Generate a unique filename
        $filename = uniqid() . '_' . $file->getClientOriginalName();

        // Store the file on the disk
        $path = $file->storeAs($folder, $filename, $this->disk);

        // Return the path to the stored file
        return ImageFile::create(['path' => $path, "name" => $file->getClientOriginalName()]);
    }

    public function deleteFile(ImageFile $file)
    {
        if ($this->fileExists($file->path))
            Storage::disk($this->disk)->delete($file->path);
        $file->delete();
    }

    protected function fileExists($filePath)
    {
        return Storage::disk($this->disk)->exists($filePath);
    }
}
