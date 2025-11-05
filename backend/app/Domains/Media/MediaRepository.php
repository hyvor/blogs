<?php declare(strict_types=1);

namespace App\Domains\Media;

use App\Data\Enums\S3TransferStateEnum;
use App\Domains\Integrations\S3\S3ConnectionDto;
use App\Domains\Integrations\S3\S3StorageService;
use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Media\Exceptions\UploadException;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Billing\LicenseService;
use App\Models\Blog;
use App\Models\Media;
use App\Domains\Blog\Jobs\UpdateMediaUrlsInPostsJob;
use App\Models\S3Storage;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;

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
        'jpg',
        'jpeg',
        'jfif',
        'pjpeg',
        'pjp',
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
    ): Collection {
        $customS3 = S3Storage::where('blog_id', $blog->id)
            ->first();

        return Media::where('blog_id', $blog->id)
            ->when($extensions, function ($query) use ($extensions) {
                $query->whereIn('extension', $extensions);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%")
                        ->orWhere('original_name', 'LIKE', "%$search%");
                });
            })
            ->where('hosted_at', $customS3 ? 'custom_s3' : 'platform')
            ->limit($limit)
            ->offset($offset)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function getOne(int $id): ?Media
    {
        return Media::find($id);
    }

    public static function getByBlogIdAndName(int $blogId, string $name): ?Media
    {
        return Media::where('blog_id', $blogId)
            ->where('name', $name)
            ->first();
    }

    public static function upload(Blog $blog, UploadedFile $file, ?int $postId = null, ?string $fileName = null): Media
    {
        if ($fileName === null) {
            $fileName = Str::random() . '.' . $file->extension();
        } else {
            $fileName = Str::kebab($fileName);
        }

        $fileName = self::getUniqueFilename($blog->id, $fileName);

        try {
            $s3connection = S3ConnectionDto::fromDefaultStorage();

            $customS3 = S3Storage::where('blog_id', $blog->id)
                ->first();

            if ($customS3) {
                // Upload to custom S3
                $s3connection = S3ConnectionDto::fromCustomStorage(
                    $customS3->endpoint_url,
                    $customS3->bucket_name,
                    $customS3->access_key,
                    decrypt($customS3->secret_key_encrypted),
                    $customS3->path_prefix,
                    $customS3->region,
                    $customS3->path_style_access,
                    $customS3->cdn_url
                );
            }

            $filesystem = (new S3StorageService())->getFilesystem($s3connection);
            
            $stream = fopen($file->getPathname(), 'r');
            try {
                $filesystem->writeStream(
                    self::getPath($blog->id, $fileName),
                    $stream
                );
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
            
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
            'hosted_at' => $customS3 ? 'custom_s3' : 'platform',
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

        if (!$response->successful()) {
            throw new UploadException();
        }

        $file = $response->body();

        // throw an exception if the file size is larger than 50MB
        if (strlen($file) > config('limits.max_media_upload_size_kb') * 1024) {
            throw new UploadException('File size is too large');
        }

        if (!$file) {
            throw new UploadException();
        }

        $size = (int) $response->header('content-size');

        return $this->createMediaFor($url, $blog, $file, $postId, $size);
    }

    public static function getContents(Media $media): ?string
    {
        $name = $media->name;
        if (!$name)
            return null;

        try {
            $customS3 = S3Storage::where('blog_id', $media->blog_id)->first();
            
            $s3connection = $customS3 && $media->hosted_at === 'custom_s3'
                ? S3ConnectionDto::fromCustomStorage(
                    $customS3->endpoint_url,
                    $customS3->bucket_name,
                    $customS3->access_key,
                    decrypt($customS3->secret_key_encrypted),
                    $customS3->path_prefix,
                    $customS3->region,
                    $customS3->path_style_access,
                    $customS3->cdn_url
                )
                : S3ConnectionDto::fromDefaultStorage();

            $filesystem = (new S3StorageService())->getFilesystem($s3connection);
            
            return $filesystem->read(self::getPath($media->blog_id, $name));
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function delete(Media $media): void
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

    private static function getPathPrefix(int $blogId): string
    {
        return "blog/$blogId";
    }

    private static function getPath(int $blogId, string $filName): string
    {
        return self::getPathPrefix($blogId) . '/' . $filName;
    }

    private static function getFileNameFromPath(string $path): string
    {
        $split = explode('/', $path);

        return $split[count($split) - 1];
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

    public static function updateName(Media $media, string $name, Blog $blog): Media
    {
        $fileName = Str::kebab($name);
        $fileName = self::getUniqueFilename($media->blog_id, $fileName);

        DB::transaction(function() use (&$media, $fileName, $blog) {

            $oldPath = self::getPath($media->blog_id, $media->name);
            $newPath = self::getPath($media->blog_id, $fileName);

            $oldLink = PermalinkRepository::getMediaPermalink($media, $blog);

            $media->name = $fileName;
            $media->save();

            $newLink = PermalinkRepository::getMediaPermalink($media, $blog);

            Storage::move($oldPath, $newPath);

            UpdateMediaUrlsInPostsJob::dispatch($blog, $oldLink, $newLink);
        });

        return $media;
    }

    public static function getUniqueFilename(int $blogId, string $name): string
    {
        $fileName = $name;

        $start = pathinfo($name, PATHINFO_FILENAME);
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        $i = 1;
        while (Storage::exists(self::getPath($blogId, $fileName))) {
            $fileName = $start . '-' . $i . '.' . $ext;
            $i++;
        }

        return $fileName;
    }

    public static function transferMediaToStorage(Blog $blog, bool $fromPlatformToCustom): void
    {
        $customS3 = S3Storage::where('blog_id', $blog->id)
            ->first();

        if (!$customS3) {
            throw new UploadException('No custom s3');
        }
        if ($fromPlatformToCustom)
            $customS3->transfer_state = S3TransferStateEnum::PENDING;
        else
            $customS3->reverse_transfer_state = S3TransferStateEnum::PENDING;

        $customS3->save();
        try {
            // Setup source and destination connections
            $sourceConnection = $fromPlatformToCustom 
                ? S3ConnectionDto::fromDefaultStorage()
                : S3ConnectionDto::fromCustomStorage(
                    $customS3->endpoint_url,
                    $customS3->bucket_name,
                    $customS3->access_key,
                    decrypt($customS3->secret_key_encrypted),
                    $customS3->path_prefix,
                    $customS3->region,
                    $customS3->path_style_access,
                    $customS3->cdn_url
                );
            
            $destConnection = $fromPlatformToCustom
                ? S3ConnectionDto::fromCustomStorage(
                    $customS3->endpoint_url,
                    $customS3->bucket_name,
                    $customS3->access_key,
                    decrypt($customS3->secret_key_encrypted),
                    $customS3->path_prefix,
                    $customS3->region,
                    $customS3->path_style_access,
                    $customS3->cdn_url
                )
                : S3ConnectionDto::fromDefaultStorage();
            
            $s3Service = new S3StorageService();
            $sourceFs = $s3Service->getFilesystem($sourceConnection);
            $destFs = $s3Service->getFilesystem($destConnection);
            
            $medias = Media
                ::where('blog_id', $blog->id)
                ->where('hosted_at', $fromPlatformToCustom ? 'platform' : 'custom_s3')
                ->get();
                
            foreach ($medias as $media) {
                $path = self::getPath($media->blog_id, $media->name);

                $stream = $sourceFs->readStream($path);
                try {
                    $destFs->writeStream($path, $stream);
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
                
                // Update the media record
                $media->hosted_at = $fromPlatformToCustom ? 'custom_s3' : 'platform';
                $media->save();
            }

            if ($fromPlatformToCustom)
                $customS3->transfer_state = S3TransferStateEnum::SUCCESS;
            else {
                $customS3->reverse_transfer_state = S3TransferStateEnum::SUCCESS;
                S3StorageService::deleteS3Storage($customS3);
            }

            $customS3->save();
        } catch (\Exception $e) {
            if ($fromPlatformToCustom)
                $customS3->transfer_state = S3TransferStateEnum::FAILED;
            else
                $customS3->reverse_transfer_state = S3TransferStateEnum::FAILED;
            $customS3->save();
        }
    }
}
