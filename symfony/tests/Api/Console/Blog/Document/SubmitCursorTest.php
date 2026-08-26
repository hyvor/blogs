<?php

namespace App\Tests\Api\Console\Blog\Document;

use App\Api\Console\Controller\DocumentsController;
use App\Service\Post\Document\DocumentService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserVariantFactory;
use App\Tests\Fake\FakeHub;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DocumentsController::class)]
#[CoversClass(DocumentService::class)]
class SubmitCursorTest extends ApiTestCase
{

    public function test_submit_cursor(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(userAttrs: [
            'cursor_color' => '#ff0000',
        ]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        UserVariantFactory::createOne([
            'user' => $user,
            'language' => $language,
            'name' => 'Test User',
        ]);
        $post = PostFactory::createOneFor($blog);
        $variant = PostVariantFactory::createOneFor($post, language: $language);

        $response = $this->consoleBlogApi(
            'POST',
            $blog,
            '/documents/cursor',
            [
                'post_variant_id' => $variant->getId(),
                'client_id' => 'test-client-id',
                'from' => 0,
                'to' => 5,
            ],
            user: $user
        );

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $hub = $this->getService(FakeHub::class);
        $updates = $hub->getPublished();
        $this->assertCount(1, $updates);

        $update = $updates[0];
        $this->assertSame(json_encode([
            'type' => 'cursor',
            'client_id' => 'test-client-id',
            'from' => 0,
            'to' => 5,
            'user' => [
                'name' => 'Test User',
                'color' => '#ff0000',
                'picture' => null,
            ],
        ]), $update->getData());
    }

}
