<?php declare(strict_types=1);

namespace App\Domains\Media;

use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Media\Exceptions\UploadException;
use App\Domains\Subscription\UsageRepository;
use App\Models\Blog;
use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
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
 */
class MediaRepository
{

    // /docs/writing
    public const IMAGE_EXTENSIONS = [
        'png',
        'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp',
        'gif',
        'apng',
        'avif',
        'svg',
        'webp',
    ];

    /**
     * @param string[]|null $extensions
     * @return Collection<int, Media>
     */
    public static function get(
        Blog $blog,
        int $limit = 0,
        int $offset = 0,
        array|null $extensions = null,
        string|null $search = null,
    ): Collection
    {
        return Media::where('blog_id', $blog->id)
            ->when($extensions, function ($query) use ($extensions) {
                $query->whereIn('extension', $extensions);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function($query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%")
                        ->orWhere('original_name', 'LIKE', "%$search%");
                });
            })
            ->limit($limit)
            ->offset($offset)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function getOne(int $id) : ?Media
    {
        return Media::find($id);
    }

    public static function getByBlogIdAndName(int $blogId, string $name) : ?Media
    {
        return Media::where('blog_id', $blogId)
            ->where('name', $name)
            ->first();
    }


    public static function upload(Blog $blog, UploadedFile $file, ?int $postId = null): Media
    {
        try {
            $prefix = self::getPathPrefix($blog->id);
            $path = Storage::putFile($prefix, $file);

            if (!$path) {
                throw new UploadException('Error while uploading from storage');
            }

            $fileName = self::getFileNameFromPath($path);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            throw new UploadException("Error while uploading: $errorMessage");
        }

        $media = Media::create([
            'blog_id' => $blog->id,
            'post_id' => $postId,
            'name' => $fileName,
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->extension(),
        ]);

        MediaCreatedEvent::dispatch($media);

        return $media;
    }

    public function uploadFromLocal(Blog $blog, string $path, ?int $postId = null): Media
    {
        $file = File::get($path);
        $size = File::size($path);
        return $this->createMediaFor($path, $blog, $file, $postId, $size);
    }

    public function uploadFromUrl(Blog $blog, string $url, ?int $postId = null): Media
    {

        try {
            $response = Http::timeout(10)->get($url);
        } catch (ConnectionException) {
            throw new UploadException('Error while fetching image file');
        }

        if (! $response->successful()) {
            throw new UploadException();
        }

        $file = $response->body();

        // throw an exception if the file size is larger than 50MB
        if (strlen($file) > config('limits.max_media_upload_size_kb') * 1024) {
            throw new UploadException('File size is too large');
        }

        if (! $file) {
            throw new UploadException();
        }

        $size = (int) $response->header('content-size');

        return $this->createMediaFor($url, $blog, $file, $postId, $size);
    }

    public static function getContents(Media $media) : ?string
    {
        $name = $media->name;
        if (!$name)
            return null;

        return Storage::get(self::getPath($media->blog_id, $name));
    }

    public static function delete(Media $media) : void
    {
        if (!$media->name)
            return;

        $path = self::getPath($media->blog_id, $media->name);

        if ($path) {
            Storage::delete($path);
        }

        $media->delete();

        MediaDeletedEvent::dispatch($media);
    }

    private static function getPathPrefix(int $blogId) : string
    {
        return "blog/$blogId";
    }

    private static function getPath(int $blogId, string $filName) : string
    {
        return self::getPathPrefix($blogId).'/'.$filName;
    }

    private static function getFileNameFromPath(string $path) : string
    {
        $split = explode('/', $path);

        return $split[count($split) - 1];
    }

    public static function hasLimitsExceeded(Blog $blog) : bool
    {
        $usage = $blog->getCount('media');
        $limit = UsageRepository::getLimitsOf($blog, 'media');
        return $usage >= $limit;
    }

    /**
     * @param string $path
     * @param Blog $blog
     * @param string $file
     * @param int|null $postId
     * @param int $size
     * @return mixed
     */
    public function createMediaFor(string $path, Blog $blog, string $file, ?int $postId, int $size)
    {
        $extension = File::extension($path);
        $name = self::getPathPrefix($blog->id) . '/' . Str::random() . ($extension ? ".$extension" : '');
        Storage::put($name, $file);

        $fileName = self::getFileNameFromPath($name);

        $media = Media::create([
            'blog_id' => $blog->id,
            'post_id' => $postId,
            'name' => $fileName,
            'size' => $size,
            'original_name' => $fileName,
            'extension' => $extension
        ]);

        MediaCreatedEvent::dispatch($media);

        return $media;
    }
}
