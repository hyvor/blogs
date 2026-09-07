<?php

namespace App\Tests\Api\Console\Blog;

use App\Api\Console\Controller\BlogController;
use App\Api\Console\Object\BlogObject;
use App\Api\Console\Object\BlogObjectFactory;
use App\Api\Console\Object\BlogVariantObject;
use App\Api\Console\Object\TagObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Api\Console\Object\UserObject;
use App\Api\Console\Object\UserObjectFactory;
use App\Entity\Enum\UserRole;
use App\Entity\Enum\UserStatus;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogObject::class)]
#[CoversClass(BlogVariantObject::class)]
#[CoversClass(BlogObjectFactory::class)]
#[CoversClass(TagObject::class)]
#[CoversClass(TagObjectFactory::class)]
#[CoversClass(UserObject::class)]
#[CoversClass(UserObjectFactory::class)]
class GetBlogTest extends ApiTestCase
{
    public function test_gets_blog(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-get']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        BlogVariantFactory::createOneForBlog($blog);

        $user = UserFactory::createOne([
            'blog' => $blog,
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);
        UserVariantFactory::createOne(['user' => $user, 'language' => $language]);

        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $language]);

        $this->consoleBlogApi('GET', $blog, '/blog', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertIsArray($json['blog']);
        $this->assertSame($blog->getSubdomain(), $json['blog']['subdomain']);
        $this->assertIsArray($json['blog']['variants']);
        $this->assertCount(1, $json['blog']['variants']);

        $this->assertIsArray($json['counts']);
        $this->assertIsArray($json['counts']['posts']);
        $this->assertSame(0, $json['counts']['posts']['published']);

        $this->assertIsArray($json['users']);
        $this->assertCount(1, $json['users']);
        $this->assertIsArray($json['users'][0]);
        $this->assertSame('admin', $json['users'][0]['role']);

        $this->assertIsArray($json['tags']);
        $this->assertCount(1, $json['tags']);

        $this->assertIsArray($json['languages']);
        $this->assertCount(1, $json['languages']);
        $this->assertIsArray($json['languages'][0]);
        $this->assertTrue($json['languages'][0]['is_primary']);

        $this->assertIsArray($json['scopes']);
        $this->assertContains('blog.read', $json['scopes']);
        $this->assertContains('languages.read', $json['scopes']);
        $this->assertContains('languages.write', $json['scopes']);
    }
}
