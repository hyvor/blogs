<?php

namespace App\Domains\Integrations\S3;

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
        public ?string $cdnUrl,
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
            cdnUrl: null,
        );
    }

    public static function fromCustomStorage(
        string $endpointUrl,
        string $bucketName,
        string $accessKey,
        string $secretKey,
        ?string $pathPrefix,
        ?string $region,
        bool $pathStyleAccess,
        ?string $cdnUrl,
    ): S3ConnectionDto
    {
        return new S3ConnectionDto(
            endpointUrl: $endpointUrl,
            bucketName: $bucketName,
            accessKey: $accessKey,
            secretKey: $secretKey,
            pathPrefix: $pathPrefix,
            region: $region,
            pathStyleAccess: $pathStyleAccess,
            cdnUrl: $cdnUrl,
        );
    }
}
