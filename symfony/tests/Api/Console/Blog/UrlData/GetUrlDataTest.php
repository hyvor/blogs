<?php

namespace App\Tests\Api\Console\Blog\UrlData;

use App\Api\Console\Controller\UrlDataController;
use App\Entity\Enum\UserStatus;
use App\Service\UrlData\UrlDataService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// mock responses from https://github.com/hyvor/unfold/blob/main/tests/Feature/UnfoldLinkTest.php

#[CoversClass(UrlDataController::class)]
#[CoversClass(UrlDataService::class)]
class GetUrlDataTest extends ApiTestCase
{
    public function test_link(): void
    {
        $mockResponse = new MockResponse(<<<HTML
<html lang="fr">
<head>
    <title>HYVOR</title>
    <meta name="description" content="We craft privacy-first, user-friendly tools for websites.">
    
    <meta name="og:url" content="https://hyvor.com">
    <meta name="og:image" content="https://hyvor.com/image.jpg">
    <meta name="og:site_name" content="HYVOR WEBSITE">
    <meta name="article:published_time" content="2021-09-01T00:00:00+00:00">
    <meta name="article:modified_time" content="2021-09-02T00:00:00+00:00">
    <meta name="article:tag" content="php">
    
    <link rel="canonical" href="https://hyvor.com">
    <link rel="icon" href="https://hyvor.com/favicon.ico">
    
    <script type="application/ld+json">
    {
        "author": [
            {
                "name": "John Doe",
                "url": "https://johndoe.com"
            }
        ]
    }
    </script>
</head>
</html>    
HTML);
        $mockClient = new MockHttpClient($mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne(['subdomain' => 'url-data']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi(
            'GET',
            $blog,
            '/url-data?url=' . urlencode('https://hyvor.com') . '&type=link',
            user: $user,
        );

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('https://hyvor.com', $json['final_url']);
        $this->assertSame('https://hyvor.com', $json['url']);
        $this->assertSame('HYVOR', $json['title']);
        $this->assertSame('We craft privacy-first, user-friendly tools for websites.', $json['description']);
        $this->assertSame('https://hyvor.com/image.jpg', $json['thumbnail_url']);
        $this->assertSame('https://hyvor.com/favicon.ico', $json['icon_url']);
        $this->assertSame('https://hyvor.com', $json['site_url']);
        $this->assertSame(1, $mockClient->getRequestsCount());
    }

    public function test_link_fail(): void
    {
        $mockResponse = new MockResponse('page not found', ['http_code' => 404]);
        $mockClient = new MockHttpClient($mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne(['subdomain' => 'url-data-fail']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi(
            'GET',
            $blog,
            '/url-data?url=' . urlencode('https://hyvor.com') . '&type=link',
            user: $user,
        );

        $this->assertResponseFailed(422, 'Unable to scrape link. HTTP status code: 404');
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

    public function test_embed(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'url-data-embed']);

        $response = $this->consoleBlogApi(
            'GET',
            $blog,
            '/url-data?url=' . urlencode('https://www.youtube.com/watch?v=Z0kGAz6HYM8') . '&type=embed',
            user: $user,
        );

        $this->assertResponseIsSuccessful();
        $html = $this->getJson()['html'];
        $this->assertIsString($html);
        $this->assertStringContainsString('<iframe', $html);
    }
}
