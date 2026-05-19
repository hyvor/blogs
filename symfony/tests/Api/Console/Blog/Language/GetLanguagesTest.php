<?php

namespace App\Tests\Api\Console\Blog\Language;

use App\Api\Console\Controller\LanguageController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
class GetLanguagesTest extends ApiTestCase
{
    public function test_get_languages(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-list'],
            ['hyvor_user_id' => 400, 'status' => 'active'],
        );
        LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'name' => 'English',
            'is_primary' => true,
            'direction' => 'ltr',
        ]);

        $authUser = AuthFake::generateUser(['id' => 400]);
        $this->consoleBlogApi('GET', 'lang-list', '/languages', user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json);
        $this->assertCount(1, $json);
        $this->assertSame('en', $json[0]['code']);
        $this->assertSame('English', $json[0]['name']);
        $this->assertTrue($json[0]['is_primary']);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-denied'],
            ['hyvor_user_id' => 408, 'status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'lang-denied', '/languages', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
