<?php

namespace App\Tests\Api\Console\Blog\Media;

use App\Api\Console\Controller\MediaController;
use App\Entity\Enum\UserStatus;
use App\Service\Integration\Unsplash\UnsplashService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(MediaController::class)]
#[CoversClass(UnsplashService::class)]
class SearchUnsplashTest extends ApiTestCase
{
    public function test_searches_unsplash(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'search-unsplash']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $responseBody = json_encode([
            'total' => 1,
            'total_pages' => 1,
            'results' => [
                [
                    'description' => 'A man drinking a coffee.',
                    'alt_description' => null,
                    'user' => [
                        'name' => 'Jeff Sheldon',
                        'links' => [
                            'html' => 'http://unsplash.com/@ugmonk',
                        ],
                    ],
                    'urls' => [
                        'regular' => 'https://images.unsplash.com/photo-1416339306562-f3d12fefd36f?ixlib=rb-0.3.5&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=1080&fit=max&s=92f3e02f63678acc8416d044e189f515',
                    ],
                ],
            ],
        ]);

        $mockClient = new MockHttpClient(new MockResponse((string)$responseBody, [
            'response_headers' => ['Content-Type' => 'application/json'],
        ]));
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $this->consoleBlogApi('GET', $blog, '/media/unsplash/search?search=test&page=1', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame(
            'https://images.unsplash.com/photo-1416339306562-f3d12fefd36f?ixlib=rb-0.3.5&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=1080&fit=max&s=92f3e02f63678acc8416d044e189f515',
            $json[0]['url'],
        );
        $this->assertSame('A man drinking a coffee.', $json[0]['title']);
        $this->assertSame('Jeff Sheldon', $json[0]['author']);
        $this->assertSame('http://unsplash.com/@ugmonk', $json[0]['author_url']);
    }
}
