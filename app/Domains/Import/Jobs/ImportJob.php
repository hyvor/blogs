<?php
namespace App\Domains\Import\Jobs;

use App\Data\Enums\ImportFormatEnum;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\UploadedFile;
use App\Domains\Media\Exceptions\UploadException;
use App\Models\Import;

class ImportJob implements ShouldQueue {

    public static function uploadFile(int $blogId, UploadedFile $file){
        try {

            $prefix = self::getPathPrefix($blogId); 

            $path = Storage::putFile($prefix, $file);

            $fileName = self::getFileNameFromPath($path);

        } catch (\Exception $e) {
            throw new UploadException('Error while uploading');
        }

        $import = Import::create([
            'blog_id' => $blogId,
            'name' => $fileName,
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->extension()
        ]);

        return $import;
    }

    private static function getPathPrefix(int $blogId) {
        return "import/$blogId";
    }

    private static function getFileNameFromPath(string $path) {
        $split = explode('/', $path);
        return $split[ count($split) - 1 ];
    }

}