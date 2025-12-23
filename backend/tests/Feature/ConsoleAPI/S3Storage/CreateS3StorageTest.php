<?php

namespace Tests\Feature\ConsoleAPI\S3Storage;

use App\Domains\Media\Jobs\TransferMediaToStorageJob;
use App\Models\S3Storage;
use Database\Factories\BlogFactory;
use Illuminate\Support\Facades\Queue;
use Tests\Case\DatabaseTestCase;

class CreateS3StorageTest extends DatabaseTestCase
{
    public function testCreateStorage(): void
    {
        Queue::fake();

        $blog = BlogFactory::withLanguageAndRoutes();
        $this->consoleApi(
            $blog,
            'POST',
            "integrations/s3",
            [
                'endpoint_url' => 'https://s3.example.com',
                'bucket_name' => 'my-bucket',
                'access_key' => 'ACCESSKEY',
                'secret_key' => 'SECRETKEY',
                'region' => 'us-east-1',
                'path_prefix' => 'media',
                'path_style_access' => true,
            ]
        );

        $s3Storage = S3Storage::where('blog_id', $blog->id)
            ->first();
        $this->assertEquals('https://s3.example.com', $s3Storage->endpoint_url);
        $this->assertEquals('my-bucket', $s3Storage->bucket_name);
        $this->assertEquals('ACCESSKEY', $s3Storage->access_key);
        $this->assertEquals('SECRETKEY', $s3Storage->secret_key_encrypted);
        $this->assertEquals('us-east-1', $s3Storage->region);
        $this->assertEquals('media', $s3Storage->path_prefix);
        $this->assertTrue($s3Storage->path_style_access);

        Queue::assertPushed(TransferMediaToStorageJob::class, function (TransferMediaToStorageJob $job) use ($blog) {
            return $job->blog_id === $blog->id && $job->fromPlatformToCustom === true;
        });
    }
}