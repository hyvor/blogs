<?php

namespace App\Tests\Api\Console\Blog\UrlData;

use App\Api\Console\Controller\UrlDataController;
use App\Entity\Enum\UserStatus;
use App\Service\UrlData\UrlDataFetchService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(UrlDataController::class)]
#[CoversClass(UrlDataFetchService::class)]
class GetUrlDataTest extends ApiTestCase
{
    public function test_gets_url_data(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'lastUrl' => 'https://hyvor.com/blogs',
            'url' => 'https://hyvor.com/blogs',
            'title' => 'Hyvor Blogs',
            'description' => 'A blogging platform',
            'thumbnailUrl' => null,
            'iconUrl' => null,
            'siteName' => 'Hyvor',
        ]) ?: '');
        $mockClient = new MockHttpClient($mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne(['subdomain' => 'url-data']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi(
            'GET',
            $blog,
            '/url-data?url=' . urlencode('https://hyvor.com/blogs') . '&type=link',
            user: $user,
        );

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('Hyvor Blogs', $json['title']);
        $this->assertSame(1, $mockClient->getRequestsCount());
    }

    public function test_validates_input(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'url-data-invalid']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi(
            'GET',
            $blog,
            '/url-data?url=not-a-url&type=link',
            user: $user,
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_throws_on_internal_api_failure(): void
    {
        $mockClient = new MockHttpClient(new MockResponse('', ['http_code' => 500]));
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne(['subdomain' => 'url-data-fail']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi(
            'GET',
            $blog,
            '/url-data?url=' . urlencode('https://hyvor.com/blogs') . '&type=link',
            user: $user,
        );

        $this->assertResponseStatusCodeSame(422);
    }
}
