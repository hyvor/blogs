<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorTalk;

use App\Api\Console\Controller\HyvorTalkController;
use App\Entity\Enum\UserRole;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\InterHyvorTalkWebsiteFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HyvorTalkController::class)]
#[CoversClass(HyvorTalkService::class)]
class GetHyvorTalkIntegrationTest extends ApiTestCase
{
    public function test_returns_disabled_when_not_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-get-disabled']);

        $response = $this->consoleBlogApi('GET', $blog, '/integrations/hyvor-talk', user: $owner);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $response->getContent(), true);
        $this->assertNull($data['data']);
    }

    public function test_returns_enabled_with_data_when_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-get-enabled']);
        InterHyvorTalkWebsiteFactory::createOne([
            'blog' => $blog,
            'website_id' => 555,
            'embed_code' => null,
        ]);

        $response = $this->consoleBlogApi('GET', $blog, '/integrations/hyvor-talk', user: $owner);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $response->getContent(), true);
        $this->assertSame(555, $data['data']['website_id']);
        $this->assertStringContainsString('hyvor-talk-comments', $data['data']['embed_code']);
    }

    public function test_requires_integrations_manage_scope(): void
    {
        [$blog, ] = BlogFactory::createOneWithUser(['subdomain' => 'ht-get-forbidden']);
        $writer = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);

        $this->consoleBlogApi('GET', $blog, '/integrations/hyvor-talk', user: $writer);

        $this->assertResponseStatusCodeSame(403);
    }
}
