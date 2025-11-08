<?php

namespace App\Domains\Integrations\S3;

use App\Models\S3Storage;

class S3ConnectionDto
{

    public function __construct(
        public string $endpointUrl,
        public string $bucketName,
        public string $accessKey,
        public string $secretKey,
        public ?string $pathPrefix,
        public ?string $region,
        public bool $pathStyleAccess,
    ) {
    }

    public static function fromDefaultStorage(): S3ConnectionDto
    {
        return new S3ConnectionDto(
            endpointUrl: config('filesystems.disks.s3.endpoint'),
            bucketName: config('filesystems.disks.s3.bucket'),
            accessKey: config('filesystems.disks.s3.key'),
            secretKey: config('filesystems.disks.s3.secret'),
            pathPrefix: null,
            region: config('filesystems.disks.s3.region'),
            pathStyleAccess: config('filesystems.disks.s3.use_path_style_endpoint', false),
        );
    }

    public static function fromCustomStorage(S3Storage $s3Storage): S3ConnectionDto
    {
        return new S3ConnectionDto(
            endpointUrl: $s3Storage->endpoint_url,
            bucketName: $s3Storage->bucket_name,
            accessKey: $s3Storage->access_key,
            secretKey: $s3Storage->getDecryptedSecretKey(),
            pathPrefix: $s3Storage->path_prefix,
            region: $s3Storage->region,
            pathStyleAccess: $s3Storage->path_style_access,
        );
    }
}
