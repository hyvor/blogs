<?php

namespace App\Tests\Api\Console\Blog\Import;

use App\Api\Console\Controller\ImportController;
use App\Api\Console\Object\Import\ImportObject;
use App\Api\Console\Object\Import\ImportedCountsObject;
use App\Service\Import\ImportService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ImportFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ImportController::class)]
#[CoversClass(ImportService::class)]
#[CoversClass(ImportObject::class)]
#[CoversClass(ImportedCountsObject::class)]
class GetImportsTest extends ApiTestCase
{
    public function test_gets_imports(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'import-get']);

        ImportFactory::createMany(3, ['blog' => $blog]);
        ImportFactory::createOne(); // other blog

        $this->consoleBlogApi('GET', $blog, '/data/imports', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json);
    }
}
