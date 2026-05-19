<?php

namespace Api\Console\Blog;

use App\Api\Console\Controller\LanguageController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
class LanguageTest extends ApiTestCase
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

    public function test_create_language(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-create'],
            ['hyvor_user_id' => 401, 'status' => 'active'],
        );

        $authUser = AuthFake::generateUser(['id' => 401]);
        $this->consoleBlogApi('POST', 'lang-create', '/languages', [
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
        $this->consoleBlogApi('POST', 'lang-dup', '/languages', [
            'code' => 'en',
            'name' => 'English Again',
            'direction' => 'ltr',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_update_language(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-update'],
            ['hyvor_user_id' => 403, 'status' => 'active'],
        );
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'name' => 'English',
            'direction' => 'ltr',
            'is_primary' => false,
        ]);

        $authUser = AuthFake::generateUser(['id' => 403]);
        $this->consoleBlogApi('PATCH', 'lang-update', '/languages/' . $lang->getId(), [
            'code' => 'en-US',
            'name' => 'English (US)',
            'direction' => 'ltr',
        ], user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('en-US', $json['code']);
        $this->assertSame('English (US)', $json['name']);
    }

    public function test_update_language_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-upd-b1'],
            ['hyvor_user_id' => 404, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-upd-b2'],
            ['hyvor_user_id' => 405, 'status' => 'active'],
        );
        $lang = LanguageFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
            'code' => 'fr',
        ]);

        $authUser = AuthFake::generateUser(['id' => 404]);
        $this->consoleBlogApi('PATCH', 'lang-upd-b1', '/languages/' . $lang->getId(), [
            'code' => 'fr',
            'name' => 'French',
            'direction' => 'ltr',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_delete_language(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-delete'],
            ['hyvor_user_id' => 406, 'status' => 'active'],
        );
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'fr',
            'is_primary' => false,
        ]);

        $authUser = AuthFake::generateUser(['id' => 406]);
        $this->consoleBlogApi('DELETE', 'lang-delete', '/languages/' . $lang->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
    }

    public function test_delete_primary_language_fails(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-del-primary'],
            ['hyvor_user_id' => 407, 'status' => 'active'],
        );
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'is_primary' => true,
        ]);

        $authUser = AuthFake::generateUser(['id' => 407]);
        $this->consoleBlogApi('DELETE', 'lang-del-primary', '/languages/' . $lang->getId(), user: $authUser);

        $this->assertResponseStatusCodeSame(422);
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
