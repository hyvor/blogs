<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorPost;

use App\Api\Console\Controller\HyvorPostController;
use App\Entity\HyvorPost;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\HyvorPostFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Helper\Fixtures;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Sdk\Auth\StaticTokenProvider;
use Hyvor\Sdk\HyvorClient;
use Hyvor\Sdk\Post\Dto\Newsletter\Newsletter;
use PHPUnit\Framework\Attributes\CoversClass;
use Sentry\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(HyvorPostController::class)]
#[CoversClass(HyvorPostService::class)]
class ConnectHyvorPostIntegrationTest extends ApiTestCase
{
    public function test_connects_and_persists_the_integration(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-connect']);
        LanguageFactory::createOnePrimaryFor($blog);
        BlogVariantFactory::createManyForBlogWithAllLanguages($blog, attributes: ['name' => 'My Blog']);

        $hpResponse = new JsonMockResponse(Fixtures::make(Newsletter::class, ['id' => 123]));
        $mockClient = new MockHttpClient($hpResponse);
        $this->getContainer()->set(HttpClientInterface::class, $mockClient);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new HyvorClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->getContainer()->set(CloudApiService::class, $cloudApiServiceMock);

        $response = $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-post/connect', user: $owner);

        $hyvorPost = $this->getEm()->getRepository(HyvorPost::class)->findOneBy(['blog' => $blog]);
        $this->assertNotNull($hyvorPost);
        $this->assertTrue($hyvorPost->isCreatedByBlogs());
        $this->assertSame(123, $hyvorPost->getNewsletterId());

        $requestBody = json_decode($hpResponse->getRequestOptions()['body'], true);
        $this->assertSame('My Blog', $requestBody['name']);
        $this->assertSame('hp-connect', $requestBody['subdomain']);
        $this->assertTrue($requestBody['autogenerate_subdomain_on_duplicate']);
        $this->assertSame('true', $requestBody['metadata']['hyvor_blogs_integration']);
        $this->assertSame((string) $blog->getId(), $requestBody['metadata']['hyvor_blogs_blog_id']);
    }

    public function test_conflicts_when_already_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-connect-conflict']);
        HyvorPostFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-post/connect', [
            'name' => 'My Newsletter',
            'subdomain' => 'my-newsletter',
        ], user: $owner);

        $this->assertResponseFailed(422, 'This blog is already connected to Hyvor Post');
    }
}
