<?php

namespace App\Tests\Api\Console\Blog\Document;

use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Fake\FakeHub;

class SubmitCursorTest extends ApiTestCase
{

    public function test_submit_cursor(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
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
        dd($update->getData());
    }

}
