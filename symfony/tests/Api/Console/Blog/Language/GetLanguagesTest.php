<?php

namespace App\Tests\Api\Console\Blog\Language;

use App\Api\Console\Controller\LanguageController;
use App\Api\Console\Object\LanguageObject;
use App\Entity\Enum\LanguageDirection;
use App\Service\Language\LanguageService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
#[CoversClass(LanguageObject::class)]
#[CoversClass(LanguageService::class)]
class GetLanguagesTest extends ApiTestCase
{
    public function test_get_languages(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-list'],
            ['status' => 'active'],
        );
        LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'en',
            'name' => 'English',
            'is_primary' => true,
            'direction' => LanguageDirection::LTR,
        ]);

        $this->consoleBlogApi('GET', 'lang-list', '/languages', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame('en', $json[0]['code']);
        $this->assertSame('English', $json[0]['name']);
        $this->assertTrue($json[0]['is_primary']);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-denied'],
            ['status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'lang-denied', '/languages', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
