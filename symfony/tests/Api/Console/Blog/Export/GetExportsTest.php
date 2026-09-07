<?php

namespace App\Tests\Api\Console\Blog\Export;

use App\Api\Console\Controller\ExportController;
use App\Api\Console\Object\ExportObject;
use App\Entity\Enum\JobStatus;
use App\Service\Export\ExportService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ExportFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ExportController::class)]
#[CoversClass(ExportService::class)]
#[CoversClass(ExportObject::class)]
class GetExportsTest extends ApiTestCase
{
    public function test_gets_exports(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'export-get']);

        ExportFactory::createOne(['blog' => $blog, 'status' => JobStatus::PENDING]);
        ExportFactory::createOne(['blog' => $blog, 'status' => JobStatus::COMPLETED, 'url' => 'https://example.com']);
        ExportFactory::createOne(['blog' => $blog, 'status' => JobStatus::FAILED, 'error' => 'Something went wrong.']);

        ExportFactory::createOne(); // other blog

        $this->consoleBlogApi('GET', $blog, '/data/exports', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame('failed', $json[0]['status']);
        $this->assertSame('Something went wrong.', $json[0]['error']);
        $this->assertIsArray($json[1]);
        $this->assertSame('completed', $json[1]['status']);
        $this->assertSame('https://example.com', $json[1]['url']);
        $this->assertIsArray($json[2]);
        $this->assertSame('pending', $json[2]['status']);
    }
}
