<?php

namespace App\Domains\Media;

use App\Domains\Media\Exceptions\UploadException;
use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 *  Terms
 * 
 *  fileName = a unique name for each file within
 *  prefix = blog/$blogId (unique for each blog)
 *  path = "blog/$blogId/$fileName"
 * 
 */
class MediaRepository
{
    public static function get(int $blogId, int $limit = 0, int $offset = 0, $extension = null): Collection
    {
        return Media::where('blog_id', $blogId)
            ->when($extension, function ($query) use ($extension) {
                $query->where('extension', $extension);
            })
            ->limit($limit)
            ->offset($offset)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function getOne(int $id)
    {
        return Media::find($id);
    }

    public static function getByBlogIdAndName(int $blogId, string $name) 
    {
        return Media::where('blog_id', $blogId)
            ->where('name', $name)
            ->first();
    }

    public static function upload(int $blogId, UploadedFile $file): Media
    {

        try {

            $prefix = self::getPathPrefix($blogId); 
            $path = Storage::putFile($prefix, $file);

            $fileName = self::getFileNameFromPath($path);

        } catch (\Exception $e) {
            throw new UploadException('Error while uploading');
        }

        $media = Media::create([
            'blog_id' => $blogId,
            'name' => $fileName,
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->extension()
        ]);

        return $media;
    }

    public static function getContents(Media $media) {
        
        $content = Storage::get( self::getPath($media->blog_id, $media->name) );
        return $content;
        
    } 

    public static function delete(int $id)
    {
        $media = self::getOne($id);
        $path = self::getPath($media->blog_id, $media->name);

        if ($path) {
            Storage::delete($path);
        }

        $media->delete();
    }

    private static function getPathPrefix(int $blogId) {
        return "blog/$blogId";
    }

    private static function getPath(int $blogId, string $filName) {
        return self::getPathPrefix($blogId) . '/' . $filName;
    }

    private static function getFileNameFromPath(string $path) {
        $split = explode('/', $path);
        return $split[ count($split) - 1 ];
    }

}
