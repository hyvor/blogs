<?php
namespace App\Domains\Media;

use App\Domains\Media\Exceptions\UploadException;
use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaRepository {

    static function get(int $blogId, int $limit = 0, int $offset = 0, $extension = null) : Collection {

        return Media::where('blog_id', $blogId)
            ->when($extension, function($query) use ($extension) {
                $query->where('extension', $extension);
            })
            ->limit($limit)
            ->offset($offset);

    }

    static function getOne(int $id) {
        return Media::find($id);
    }

    static function upload(int $blogId, UploadedFile $file) : Media {
        
        try {
            $path = Storage::putFile("blog/$blogId", $file);
            $url = Storage::url($path);
        } catch (\Exception $e) {
            throw new UploadException('Error while uploading');
        }

        $media = Media::create([
            'blog_id' => $blogId,
            'url' => $url,
            'size' => $file->getSize(),
            'name' => $file->getClientOriginalName(),
            'extension' => $file->extension()
        ]);

        return $media;
    }

    static function delete(int $id) {
        self::getOne($id)->delete();
    }

}