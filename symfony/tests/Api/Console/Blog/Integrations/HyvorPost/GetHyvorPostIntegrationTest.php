<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorPost;

use App\Api\Console\Controller\HyvorPostController;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use App\Tests\Factory\UserFactory;
use App\Entity\Enum\UserRole;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HyvorPostController::class)]
#[CoversClass(HyvorPostService::class)]
class GetHyvorPostIntegrationTest extends ApiTestCase
{
    public function test_returns_disabled_when_not_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-get-disabled']);

        $response = $this->consoleBlogApi('GET', $blog, '/integrations/hyvor-post', user: $owner);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['enabled']);
        $this->assertNull($data['data']);
    }

    public function test_returns_enabled_with_data_when_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-get-enabled']);
        $hyvorPost = HyvorPostFactory::createOne([
            'blog' => $blog,
            'newsletter_id' => 555,
            'subdomain' => 'hp-get-enabled-newsletter',
            'embed_code' => null,
        ]);

        $response = $this->consoleBlogApi('GET', $blog, '/integrations/hyvor-post', user: $owner);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $response->getContent(), true);
        $this->assertTrue($data['enabled']);
        $this->assertSame(555, $data['data']['newsletter_id']);
        $this->assertSame('hp-get-enabled-newsletter', $data['data']['subdomain']);
        $this->assertStringContainsString('hyvor-post-form', $data['data']['embed_code']);
    }

    public function test_requires_integrations_manage_scope(): void
    {
        [$blog, ] = BlogFactory::createOneWithUser(['subdomain' => 'hp-get-forbidden']);
        $writer = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);

        $this->consoleBlogApi('GET', $blog, '/integrations/hyvor-post', user: $writer);

        $this->assertResponseStatusCodeSame(403);
    }
}
