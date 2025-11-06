<?php

namespace Tests\Feature\ConsoleAPI\S3Storage;

use App\Domains\Media\Jobs\TransferMediaToStorageJob;
use Database\Factories\BlogFactory;
use Database\Factories\S3StorageFactory;
use Illuminate\Support\Facades\Queue;
use Tests\Case\DatabaseTestCase;

class DeleteS3StorageTest extends DatabaseTestCase
{
    public function testDeleteWhenNoS3StorageExists(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();

        $response = $this->consoleApi(
            $blog,
            'DELETE',
            "integrations/s3"
        );

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }

    public function testDeleteS3Storage(): void
    {
        Queue::fake();

        $blog = BlogFactory::withLanguageAndRoutes();
        S3StorageFactory::forBlog($blog, [
            'endpoint_url' => 'https://s3.example.com',
            'bucket_name' => 'my-bucket',
            'access_key' => 'ACCESSKEY',
            'secret_key_encrypted' => encrypt('SECRETKEY'),
            'region' => 'us-east-1',
            'path_prefix' => 'media',
            'path_style_access' => true,
            'cdn_url' => 'https://cdn.example.com',
        ]);

        $response = $this->consoleApi(
            $blog,
            'DELETE',
            "integrations/s3"
        );

        $response->assertStatus(200);
        $response->assertExactJson([]);

        Queue::assertPushed(TransferMediaToStorageJob::class, function (TransferMediaToStorageJob $job) use ($blog) {
            return $job->blog_id === $blog->id && $job->fromPlatformToCustom === false;
        });
    }
}
