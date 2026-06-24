<?php

namespace App\Tests\Api\Console\Blog\Media;

use App\Api\Console\Controller\MediaController;
use App\Entity\Enum\UserStatus;
use App\Service\Media\MediaService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(MediaController::class)]
#[CoversClass(MediaService::class)]
class UploadFromUrlTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::getContainer()->set(Filesystem::class, new Filesystem(new InMemoryFilesystemAdapter()));
    }

    private function enableBilling(int $orgId): void
    {
        $license = BlogsLicense::trial();
        $license->storage = 1000;
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [$orgId => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)],
        );
    }

    public function test_uploads_from_url(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'upload-from-url', 'organization_id' => 2001]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->enableBilling(2001);

        $mockClient = new MockHttpClient(new MockResponse('test', [
            'response_headers' => ['Content-Type' => 'text/plain'],
        ]));
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $this->consoleBlogApi('POST', $blog, '/media/from-url', [
            'url' => 'https://example.com/image.txt',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertGreaterThan(0, $json['id']);
        $this->assertIsString($json['url']);
        $this->assertStringContainsString('/media', $json['url']);
    }

    public function test_rejects_uploading_larger_files(): void
    {
        $previousLimit = ini_set('memory_limit', '512M');

        $blog = BlogFactory::createOne(['subdomain' => 'upload-from-url-large', 'organization_id' => 2002]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->enableBilling(2002);

        $mockClient = new MockHttpClient(new MockResponse(str_repeat('a', 50_000_001), [
            'response_headers' => ['Content-Type' => 'text/plain'],
        ]));
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $this->consoleBlogApi('POST', $blog, '/media/from-url', [
            'url' => 'https://example.com/image.txt',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'File size is too large',
            (string)$this->client->getResponse()->getContent(),
        );

        ini_set('memory_limit', $previousLimit === false ? '128M' : $previousLimit);
    }

    public function test_throws_error_when_storage_limit_exceeded(): void
    {
        $blog = BlogFactory::createOne([
            'subdomain' => 'upload-from-url-limit',
            'organization_id' => 2003,
            'counts' => ['media' => 10 ** 9 * 2],
        ]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->enableBilling(2003);

        $this->consoleBlogApi('POST', $blog, '/media/from-url', [
            'url' => 'https://test.com',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'Total storage limit exceeded. Please upgrade your plan.',
            (string)$this->client->getResponse()->getContent(),
        );
    }
}
