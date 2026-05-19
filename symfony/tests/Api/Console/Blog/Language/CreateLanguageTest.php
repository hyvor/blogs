<?php

namespace App\Tests\Api\Console\Blog\Language;

use App\Api\Console\Controller\LanguageController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
class CreateLanguageTest extends ApiTestCase
{
    public function test_create_language(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-create'],
            ['hyvor_user_id' => 401, 'status' => 'active'],
        );

        $authUser = AuthFake::generateUser(['id' => 401]);
        $this->consoleBlogApi('POST', 'lang-create', '/language', [
            'code' => 'fr',
            'name' => 'French',
            'direction' => 'ltr',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('fr', $json['code']);
        $this->assertSame('French', $json['name']);
    }

    public function test_create_language_duplicate_code(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-dup'],
            ['hyvor_user_id' => 402, 'status' => 'active'],
        );
        LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
        ]);

        $authUser = AuthFake::generateUser(['id' => 402]);
        $this->consoleBlogApi('POST', 'lang-dup', '/language', [
            'code' => 'en',
            'name' => 'English Again',
            'direction' => 'ltr',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }
}
