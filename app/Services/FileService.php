<?php

namespace App\Services;

use App\Models\ImageFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * The disk where files are stored.
     *
     * @var string
     */
    protected $disk = 'public';

    /**
     * Upload a file and store its path in the database.
     *
     * @param UploadedFile $file The file to upload.
     * @param string $folder The folder within the disk where the file should be stored.
     * @return ImageFile The database record for the uploaded file.
     */
    public function upload(UploadedFile $file, $folder = 'images'): ImageFile
    {
        $extension = $file->getClientOriginalExtension();
        $secureFileName = md5($file->getClientOriginalName() . microtime()) . '.' . $extension;
        $path = $file->store('data', $secureFileName, 'uploads');

        return ImageFile::create([
            'path' => $path,
            'name' => $secureFileName
        ]);
    }

    /**
     * Delete a file from storage and remove its record from the database.
     *
     * @param ImageFile $file The file record to delete.
     * @return void
     */
    public function deleteFile(ImageFile $file): void
    {
        $path = 'data/' . $file->name;
        if ($this->fileExists($path))
            Storage::disk($this->disk)->delete($path);

        $file->delete();
    }

    /**
     * Check if a file exists in the storage.
     *
     * @param string $filePath The path of the file to check.
     * @return bool True if the file exists, false otherwise.
     */
    protected function fileExists(string $filePath): bool
    {
        return Storage::disk('uploads')->exists($filePath);
    }

    /**
     * Check if a file exists in the storage.
     *
     * @param string $filePath The path of the file to check.
     * @return bool True if the file exists, false otherwise.
     */
    public function getFile(ImageFile $file): bool
    {
        $path = 'data/' . $file->name;
        if (!Storage::disk('uploads')->exists($path))
            abort(404);

        return Storage::disk('uploads')->download($path);
    }
}
