<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Service\Limit;
use App\Service\Post\PostService;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
#[CoversClass(UserService::class)]
class UpdatePostAuthorsTest extends ApiTestCase
{
    public function test_updates_post_authors(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-authors-update']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $author2 = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/authors', [
            'ids' => [$user->getId(), $author2->getId()],
        ], user: $user);

        $this->assertResponseIsSuccessful();

        $this->getEm()->refresh($post);
        $this->assertCount(2, $post->getAuthors());
    }

    public function test_fails_if_author_belongs_to_different_blog(): void
    {
        $blog1 = BlogFactory::createOne(['subdomain' => 'post-authors-blog1']);
        $blog2 = BlogFactory::createOne(['subdomain' => 'post-authors-blog2']);
        $user1 = UserFactory::createOne(['blog' => $blog1, 'status' => 'active']);
        $userFromBlog2 = UserFactory::createOne(['blog' => $blog2, 'status' => 'active']);
        $language = LanguageFactory::createOnePrimaryFor($blog1);
        $post = PostFactory::createOne(['blog' => $blog1]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('PATCH', $blog1, '/post/' . $post->getId() . '/authors', [
            'ids' => [$userFromBlog2->getId()],
        ], user: $user1);

        $this->assertResponseFailed(422, 'Some author IDs are invalid');
    }

    public function test_fails_if_more_than_max_authors(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-authors-limit']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $post = PostFactory::createOne(['blog' => $blog]);

        $authorIds = array_fill(0, Limit::MAX_AUTHORS_PER_POST + 1, 1);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/authors', [
            'ids' => $authorIds,
        ], user: $user);

        $this->assertResponseFailed(422, 'You can assign a maximum of ' . Limit::MAX_AUTHORS_PER_POST . ' authors to a post.');
    }
}
