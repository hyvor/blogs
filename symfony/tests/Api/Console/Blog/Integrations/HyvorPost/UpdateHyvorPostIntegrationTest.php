<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorPost;

use App\Api\Console\Controller\HyvorPostController;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HyvorPostController::class)]
#[CoversClass(HyvorPostService::class)]
class UpdateHyvorPostIntegrationTest extends ApiTestCase
{
    public function test_updates_the_embed_code(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-update-ok']);
        HyvorPostFactory::createOne(['blog' => $blog, 'embed_code' => null]);

        $response = $this->consoleBlogApi('PATCH', $blog, '/integrations/hyvor-post', [
            'embed_code' => '<custom-embed></custom-embed>',
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertSame('<custom-embed></custom-embed>', $data['embed_code']);
    }

    public function test_resets_to_default_with_null(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-update-reset']);
        HyvorPostFactory::createOne(['blog' => $blog, 'embed_code' => '<custom-embed></custom-embed>']);

        $response = $this->consoleBlogApi('PATCH', $blog, '/integrations/hyvor-post', [
            'embed_code' => null,
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $response->getContent(), true);
        $this->assertIsArray($data);
        $embedCode = $data['embed_code'];
        $this->assertIsString($embedCode);
        $this->assertStringContainsString('hyvor-post-form', $embedCode);
    }

    public function test_404_when_not_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-update-missing']);

        $this->consoleBlogApi('PATCH', $blog, '/integrations/hyvor-post', [
            'embed_code' => 'x',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(404);
    }
}
