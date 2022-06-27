<?php

namespace App\Domains\Media;

use App\Domains\Media\Exceptions\UploadException;
use App\Models\Blog;
use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    public static function get(Blog $blog, int $limit = 0, int $offset = 0, string|null $extension = null): Collection
    {
        return Media::where('blog_id', $blog->id)
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

    public static function upload(Blog $blog, UploadedFile $file): Media
    {
        try {
            $prefix = self::getPathPrefix($blog->id);
            $path = Storage::putFile($prefix, $file);

            $fileName = self::getFileNameFromPath($path);
        } catch (\Exception $e) {
            throw new UploadException('Error while uploading');
        }

        return Media::create([
            'blog_id' => $blog->id,
            'name' => $fileName,
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->extension(),
        ]);
    }

    public static function uploadFromUrl(Blog $blog, string $url): Media
    {
        $response = Http::get($url);

        if (! $response->successful()) {
            throw new UploadException();
        }

        $file = $response->body();

        if (! $file) {
            throw new UploadException();
        }

        $size = (int) $response->header('content-size');

        $extension = File::extension($url);
        $name = self::getPathPrefix($blog->id) . '/' . Str::random() . ($extension ? ".$extension" : "");
        Storage::put($name, $file);

        $fileName = self::getFileNameFromPath($name);

        return Media::create([
            'blog_id' => $blog->id,
            'name' => $fileName,
            'size' => $size,
            'original_name' => $fileName,
        ]);
    }

    public static function getContents(Media $media)
    {
        $content = Storage::get(self::getPath($media->blog_id, $media->name));

        return $content;
    }

    public static function delete(Media $media)
    {
        $path = self::getPath($media->blog_id, $media->name);

        if ($path) {
            Storage::delete($path);
        }

        $media->delete();
    }

    private static function getPathPrefix(int $blogId)
    {
        return "blog/$blogId";
    }

    private static function getPath(int $blogId, string $filName)
    {
        return self::getPathPrefix($blogId) . '/' . $filName;
    }

    private static function getFileNameFromPath(string $path)
    {
        $split = explode('/', $path);

        return $split[ count($split) - 1 ];
    }
}
