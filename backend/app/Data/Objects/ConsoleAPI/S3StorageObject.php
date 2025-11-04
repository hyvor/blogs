<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\S3Storage;

class S3StorageObject
{
    public string $endpointUrl;

    public string $bucketName;

    public string $accessKey;

    public string $secretKey;

    public ?string $region;

    public ?string $pathPrefix;

    public bool $pathStyleAccess;

    public ?string $cdnUrl;

    public function __construct(S3Storage $s3Storage) {
        $this->endpointUrl = $s3Storage->endpoint_url;
        $this->bucketName = $s3Storage->bucket_name;
        $this->accessKey = $s3Storage->access_key;
        $this->secretKey = decrypt($s3Storage->secret_key_encrypted);
        $this->region = $s3Storage->region;
        $this->pathPrefix = $s3Storage->path_prefix;
        $this->pathStyleAccess = $s3Storage->path_style_access;
        $this->cdnUrl = $s3Storage->cdn_url;
    }
}
