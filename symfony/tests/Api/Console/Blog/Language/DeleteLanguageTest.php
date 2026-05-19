<?php

namespace App\Tests\Api\Console\Blog\Language;

use App\Api\Console\Controller\LanguageController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
class DeleteLanguageTest extends ApiTestCase
{
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
        $this->consoleBlogApi('DELETE', 'lang-delete', '/language/' . $lang->getId(), user: $authUser);

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
        $this->consoleBlogApi('DELETE', 'lang-del-primary', '/language/' . $lang->getId(), user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }
}
