<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Post;
use App\Entity\Enum\PostVariantStatus;
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
class ClonePostTest extends ApiTestCase
{
    public function test_clones_post_with_variants_tags_and_authors(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-clone']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $tag = TagFactory::createOne(['blog' => $blog]);

        $original = PostFactory::createOne([
            'blog' => $blog,
            'is_featured' => true,
            'featured_image_url' => 'https://example.com/img.jpg',
            'code_head' => '<meta>',
            'code_foot' => '<footer>',
        ]);
        PostVariantFactory::createOne([
            'post' => $original,
            'language' => $language,
            'title' => 'Original Title',
            'slug' => 'original-slug',
            'status' => PostVariantStatus::PUBLISHED,
        ]);
        $original->getTags()->add($tag);
        $original->getAuthors()->add($user);
        $this->getEm()->flush();

        $this->consoleBlogApi('POST', $blog, '/post/' . $original->getId() . '/clone', user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();

        $this->assertNotSame($original->getId(), $json['id']);
        $this->assertFalse($json['is_featured']);
        $this->assertNull($json['published_at']);
        $this->assertSame('https://example.com/img.jpg', $json['featured_image_url']);
        $this->assertSame('<meta>', $json['code_head']);

        $this->assertCount(1, $json['variants']);
        $this->assertSame('draft', $json['variants'][0]['status']);
        $this->assertNull($json['variants'][0]['slug']);
        $this->assertSame('Original Title', $json['variants'][0]['title']);

        $this->assertCount(1, $json['tags']);
        $this->assertCount(1, $json['authors']);

        $posts = $this->getEm()->getRepository(Post::class)->findBy(['blog' => $blog]);
        $this->assertCount(2, $posts);
    }
}
