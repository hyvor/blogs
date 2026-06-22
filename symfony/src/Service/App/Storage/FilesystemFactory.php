<?php

namespace App\Service\App\Storage;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

class FilesystemFactory
{
    /** @param 's3'|'memory' $adapterType */
    public static function create(
        string $adapterType,
        S3Client $s3Client,
        ?string $bucket,
    ): Filesystem {
        if ($adapterType === 's3') {
            $adapter = new AwsS3V3Adapter($s3Client, $bucket ?? '');
        } else {
            $adapter = new InMemoryFilesystemAdapter();
        }

        return new Filesystem($adapter);
    }
}
