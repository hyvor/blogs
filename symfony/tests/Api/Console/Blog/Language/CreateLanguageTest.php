<?php

namespace App\Tests\Api\Console\Blog\Language;

use App\Api\Console\Controller\LanguageController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
class CreateLanguageTest extends ApiTestCase
{
    public function test_create_language(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-create'],
            ['status' => 'active'],
        );

        $this->consoleBlogApi('POST', 'lang-create', '/language', [
            'code' => 'fr',
            'name' => 'French',
            'direction' => 'ltr',
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('fr', $json['code']);
        $this->assertSame('French', $json['name']);
    }

    public function test_create_language_duplicate_code(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-dup'],
            ['status' => 'active'],
        );
        LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
        ]);

        $this->consoleBlogApi('POST', 'lang-dup', '/language', [
            'code' => 'en',
            'name' => 'English Again',
            'direction' => 'ltr',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }
}
