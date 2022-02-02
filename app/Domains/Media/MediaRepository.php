<?php

namespace App\Domains\Media;

use App\Domains\Media\Exceptions\UploadException;
use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    public static function upload(int $blogId, UploadedFile $file): Media
    {

        try {
            $path = Storage::putFile("blog/$blogId", $file);
            $url = Storage::url($path);
        } catch (\Exception $e) {
            throw new UploadException('Error while uploading');
        }

        $media = Media::create([
            'blog_id' => $blogId,
            'path' => $path,
            'url' => $url,
            'size' => $file->getSize(),
            'name' => $file->getClientOriginalName(),
            'extension' => $file->extension()
        ]);

        return $media;
    }

    public static function delete(int $id)
    {
        $media = self::getOne($id);
        $path = $media->path;

        if ($path) {
            Storage::delete($path);
        }

        $media->delete();
    }
}
