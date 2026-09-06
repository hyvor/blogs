<?php

namespace App\Tests\Service\App\Storage;

use App\Service\App\Storage\FilesystemFactory;
use AsyncAws\S3\S3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilesystemFactory::class)]
class FilesystemFactoryTest extends TestCase
{

    private function getAdapter(Filesystem $filesystem): object
    {
        $property = new \ReflectionProperty(Filesystem::class, 'adapter');
        $adapter = $property->getValue($filesystem);
        $this->assertIsObject($adapter);
        return $adapter;
    }

    public function test_creates_s3_adapter(): void
    {
        $filesystem = FilesystemFactory::create('s3', new S3Client(), 'my-bucket');
        $this->assertInstanceOf(AsyncAwsS3Adapter::class, $this->getAdapter($filesystem));
    }

    public function test_creates_local_file_adapter(): void
    {
        $filesystem = FilesystemFactory::create('file', new S3Client(), null);
        $this->assertInstanceOf(LocalFilesystemAdapter::class, $this->getAdapter($filesystem));
    }

    public function test_creates_in_memory_adapter_by_default(): void
    {
        $filesystem = FilesystemFactory::create('memory', new S3Client(), null);
        $this->assertInstanceOf(InMemoryFilesystemAdapter::class, $this->getAdapter($filesystem));
    }

}
