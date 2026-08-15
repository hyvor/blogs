<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorTalk;

use App\Api\Console\Controller\HyvorTalkController;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\InterHyvorTalkWebsiteFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HyvorTalkController::class)]
#[CoversClass(HyvorTalkService::class)]
class UpdateHyvorTalkIntegrationTest extends ApiTestCase
{
    public function test_updates_the_embed_code(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-update-ok']);
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'embed_code' => null]);

        $response = $this->consoleBlogApi('PATCH', $blog, '/integrations/hyvor-talk', [
            'embed_code' => '<custom-embed></custom-embed>',
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $response->getContent(), true);
        $this->assertSame('<custom-embed></custom-embed>', $data['embed_code']);
    }

    public function test_resets_to_default_with_null(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-update-reset']);
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'embed_code' => '<custom-embed></custom-embed>']);

        $response = $this->consoleBlogApi('PATCH', $blog, '/integrations/hyvor-talk', [
            'embed_code' => null,
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $response->getContent(), true);
        $this->assertStringContainsString('hyvor-talk-comments', $data['embed_code']);
    }

    public function test_404_when_not_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-update-missing']);

        $this->consoleBlogApi('PATCH', $blog, '/integrations/hyvor-talk', [
            'embed_code' => 'x',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(404);
    }
}
