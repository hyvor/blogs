<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
class UpdatePostTagsTest extends ApiTestCase
{
    public function test_updates_post_tags(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-tags-update']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $tag1 = TagFactory::createOne(['blog' => $blog]);
        $tag2 = TagFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/tags', [
            'ids' => [$tag1->getId(), $tag2->getId()],
        ], user: $user);

        $this->assertResponseIsSuccessful();

        $this->getEm()->refresh($post);
        $this->assertCount(2, $post->getTags());
    }

    public function test_clears_tags_when_empty_ids(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-tags-clear']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $tag = TagFactory::createOne(['blog' => $blog]);
        $post->getTags()->add($tag);
        $this->getEm()->flush();

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/tags', [
            'ids' => [],
        ], user: $user);

        $this->assertResponseIsSuccessful();

        $this->getEm()->refresh($post);
        $this->assertCount(0, $post->getTags());
    }

    public function test_fails_if_tag_belongs_to_different_blog(): void
    {
        $blog1 = BlogFactory::createOne(['subdomain' => 'post-tags-blog1']);
        $blog2 = BlogFactory::createOne(['subdomain' => 'post-tags-blog2']);
        $user = UserFactory::createOne(['blog' => $blog1, 'status' => 'active']);
        $language = LanguageFactory::createOnePrimaryFor($blog1);
        $post = PostFactory::createOne(['blog' => $blog1]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $tag = TagFactory::createOne(['blog' => $blog2]);

        $this->consoleBlogApi('PATCH', $blog1, '/post/' . $post->getId() . '/tags', [
            'ids' => [$tag->getId()],
        ], user: $user);

        $this->assertResponseFailed(422, 'Some tag IDs are invalid');
    }
}
