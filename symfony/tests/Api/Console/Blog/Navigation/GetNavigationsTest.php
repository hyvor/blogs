<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\NavigationFactory;
use App\Tests\Factory\NavigationVariantFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
class GetNavigationsTest extends ApiTestCase
{
    public function test_get_navigations(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-list'],
            ['hyvor_user_id' => 300, 'status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'url' => '/nav-list.json',
            'type' => 'header',
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
            'navigation_id' => $nav->getId(),
            'language' => $lang,
            'language_id' => $lang->getId(),
            'name' => 'Main Nav',
        ]);

        $authUser = AuthFake::generateUser(['id' => 300]);
        $this->consoleBlogApi('GET', 'nav-list', '/navigations', user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json);
        $this->assertCount(1, $json);
        $this->assertSame('/nav-list.json', $json[0]['url']);
        $this->assertSame('header', $json[0]['type']);
        $this->assertIsArray($json[0]['variants']);
        $this->assertCount(1, $json[0]['variants']);
        $this->assertSame('Main Nav', $json[0]['variants'][0]['name']);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-denied'],
            ['hyvor_user_id' => 308, 'status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'nav-denied', '/navigations', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
