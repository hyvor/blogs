<?php

namespace App\Domains\Import;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Domains\Media\Exceptions\UploadException;
use App\Models\Import;
use App\Models\Blog;
use App\Data\Enums\ImportFormatEnum;

class UploadRepository
{
    public static function uploadFile(ImportFormatEnum $platform, Blog $blog, UploadedFile $file)
    {
        // $prefix = 'public/import/$blogId'; 
        // $path = Storage::putFile($prefix, $file);
        $path = Storage::disk('local')->put( 'import/'.$file , $file);
        $fileName = self::getFileNameFromPath($path);

        $import = Import::create([
            'blog_id' => $blog->id,
            'name' => $fileName,
            'type' => $platform,
        ]);

        // self::getImportBlogId($import->blog_id);
        return $import;
    }

    private static function getFileNameFromPath(string $path) {
        $split = explode('/', $path);
        return $split[ count($split) - 1 ];
    }

    private static function getImportBlogId(int $blogId) {
        return $blogId;
    }
}