<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\S3Storage;

class S3StorageObject
{
    public string $endpoint_url;

    public string $bucket_name;

    public string $access_key;

    public string $secret_key;

    public ?string $region;

    public bool $path_style_access;

    public ?string $cdn_url;

    public function __construct(S3Storage $s3Storage) {
        $this->endpoint_url = $s3Storage->endpoint_url;
        $this->bucket_name = $s3Storage->bucket_name;
        $this->access_key = $s3Storage->access_key;
        $this->secret_key = decrypt($s3Storage->secret_key_encrypted);
        $this->region = $s3Storage->region;
        $this->path_style_access = $s3Storage->path_style_access;
        $this->cdn_url = $s3Storage->cdn_url;
    }
}
