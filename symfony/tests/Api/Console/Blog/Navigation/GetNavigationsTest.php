<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Api\Console\Object\NavigationObject;
use App\Api\Console\Object\NavigationVariantObject;
use App\Entity\Enum\NavigationType;
use App\Service\Navigation\NavigationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\NavigationFactory;
use App\Tests\Factory\NavigationVariantFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
#[CoversClass(NavigationObject::class)]
#[CoversClass(NavigationService::class)]
#[CoversClass(NavigationVariantObject::class)]
class GetNavigationsTest extends ApiTestCase
{
    public function test_get_navigations(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-list'],
            ['status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'url' => '/nav-list.json',
            'type' => NavigationType::HEADER,
            'sort' => 0,
        ]);
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'is_primary' => true,
        ]);
        NavigationVariantFactory::createOne([
            'navigation' => $nav,
            'language' => $lang,
            'language_id' => $lang->getId(),
            'name' => 'Main Nav',
        ]);

        $this->consoleBlogApi('GET', 'nav-list', '/navigations', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame('/nav-list.json', $json[0]['url']);
        $this->assertSame('header', $json[0]['type']);
        $this->assertIsArray($json[0]['variants']);
        $this->assertCount(1, $json[0]['variants']);
        $this->assertIsArray($json[0]['variants'][0]);
        $this->assertSame('Main Nav', $json[0]['variants'][0]['name']);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-denied'],
            ['status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'nav-denied', '/navigations', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
