<?php

namespace Tests\Feature\ConsoleAPI\S3Storage;

use App\Domains\Integrations\S3\S3StorageService;
use Database\Factories\BlogFactory;
use Tests\Case\DatabaseTestCase;

class TestS3StorageTest extends DatabaseTestCase
{
    public function testS3StorageConnectionSuccess(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();

        $mockS3Service = $this->createMock(S3StorageService::class);
        $mockS3Service->method('test')->willReturn([
            'write' => true,
            'read' => true,
            'delete' => true,
            'errors' => [],
        ]);

        $this->app->instance(S3StorageService::class, $mockS3Service);

        $response = $this->consoleApi(
            $blog,
            'POST',
            'integrations/s3/test-connection',
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

        $response->assertStatus(200);
        $response->assertExactJson([
            'write' => true,
            'read' => true,
            'delete' => true,
            'errors' => [],
        ]);
    }

    public function testS3StorageConnectionWithErrors(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();

        $mockS3Service = $this->createMock(S3StorageService::class);
        $mockS3Service->method('test')->willReturn([
            'write' => false,
            'read' => false,
            'delete' => false,
            'errors' => [
                'write' => 'Unable to write file',
                'read' => 'Unable to read file',
                'delete' => 'Unable to delete file',
            ],
        ]);

        $this->app->instance(S3StorageService::class, $mockS3Service);

        $response = $this->consoleApi(
            $blog,
            'POST',
            'integrations/s3/test-connection',
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

        $response->assertStatus(200);
        $response->assertExactJson([
            'write' => false,
            'read' => false,
            'delete' => false,
            'errors' => [
                'write' => 'Unable to write file',
                'read' => 'Unable to read file',
                'delete' => 'Unable to delete file',
            ],
        ]);
    }
}
