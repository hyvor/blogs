<?php

namespace Tests\Feature\ConsoleAPI\S3Storage;

use App\Models\S3Storage;
use Database\Factories\BlogFactory;
use Database\Factories\S3StorageFactory;
use Tests\Case\DatabaseTestCase;

class GetS3StorageTest extends DatabaseTestCase
{
    public function testGetS3StorageWhenNotSet(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();

        $response = $this->consoleApi(
            $blog,
            'GET',
            "integrations/s3"
        );

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }

    public function testGetS3StorageWhenSet(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();
        S3StorageFactory::forBlog($blog, [
            'endpoint_url' => 'https://s3.example.com',
            'bucket_name' => 'my-bucket',
            'access_key' => 'ACCESSKEY',
            'secret_key_encrypted' => 'SECRETKEY',
            'region' => 'us-east-1',
            'path_prefix' => 'media',
            'path_style_access' => true,
        ]);

        $response = $this->consoleApi(
            $blog,
            'GET',
            "integrations/s3"
        );

        $response->assertStatus(200);
        $response->assertExactJson([
            'endpoint_url' => 'https://s3.example.com',
            'bucket_name' => 'my-bucket',
            'access_key' => 'ACCESSKEY',
            'secret_key' => 'SECRETKEY',
            'region' => 'us-east-1',
            'path_prefix' => 'media',
            'path_style_access' => true,
        ]);
    }
}
