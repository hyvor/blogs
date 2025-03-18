<?php

namespace App\Domains\Integrations\S3;

class S3ConnectionDto
{

    public function __construct(
        public string $endpointUrl,
        public string $bucketName,
        public string $accessKey,
        public string $secretKey,
        public ?string $region,
        public ?string $pathPrefix,
        public bool $pathStyleAccess,
        public ?string $cdnUrl,
    ) {
    }

}