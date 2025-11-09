<?php

namespace App\Domains\Media\Services;

use App\Data\Enums\MediaHostedAtEnum;
use App\Data\Enums\S3TransferStateEnum;
use App\Domains\Integrations\S3\S3ConnectionDto;
use App\Domains\Integrations\S3\S3StorageService;
use App\Domains\Media\Exceptions\UploadException;
use App\Domains\Media\MediaRepository;
use App\Models\Blog;
use App\Models\Media;
use App\Models\S3Storage;

class MediaTransfer
{

    public static function transferMediaToStorage(Blog $blog, bool $fromPlatformToCustom): void
    {
        $customS3 = S3Storage::fromBlogId($blog->id);

        if (!$customS3) {
            throw new UploadException('No custom s3');
        }
        if ($fromPlatformToCustom)
            $customS3->transfer_state = S3TransferStateEnum::PENDING;
        else
            $customS3->reverse_transfer_state = S3TransferStateEnum::PENDING;

        $customS3->save();
        try {
            $sourceConnection = $fromPlatformToCustom
                ? S3ConnectionDto::fromDefaultStorage()
                : S3ConnectionDto::fromCustomStorage($customS3);
            $destConnection = $fromPlatformToCustom
                ? S3ConnectionDto::fromCustomStorage($customS3)
                : S3ConnectionDto::fromDefaultStorage();

            $s3Service = new S3StorageService();
            $sourceFs = $s3Service->getFilesystem($sourceConnection);
            $destFs = $s3Service->getFilesystem($destConnection);

            Media::where('blog_id', $blog->id)
                ->where('hosted_at', $fromPlatformToCustom ? MediaHostedAtEnum::PLATFORM : MediaHostedAtEnum::CUSTOM_S3)
                ->chunkById(100, function ($medias) use ($sourceFs, $destFs, $fromPlatformToCustom) {
                    foreach ($medias as $media) {
                        $path = MediaRepository::getPath($media->blog_id, $media->name);

                        $stream = $sourceFs->readStream($path);

                        try {
                            $destFs->writeStream($path, $stream);
                        } finally {
                            if (is_resource($stream)) {
                                fclose($stream);
                            }
                        }

                        $media->hosted_at = $fromPlatformToCustom
                            ? MediaHostedAtEnum::CUSTOM_S3
                            : MediaHostedAtEnum::PLATFORM;

                        $media->save();
                    }
                });

            if ($fromPlatformToCustom) {
                $customS3->transfer_state = S3TransferStateEnum::SUCCESS;
                $customS3->save();
            }
            else {
                $customS3->reverse_transfer_state = S3TransferStateEnum::SUCCESS;
                $customS3->save();
                $customS3->delete();
            }

        } catch (\Exception $e) {
            if ($fromPlatformToCustom)
                $customS3->transfer_state = S3TransferStateEnum::FAILED;
            else
                $customS3->reverse_transfer_state = S3TransferStateEnum::FAILED;
            $customS3->save();
        }
    }
}
