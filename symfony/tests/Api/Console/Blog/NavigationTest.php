<?php

namespace Api\Console\Blog;

use App\Api\Console\Controller\NavigationController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\NavigationFactory;
use App\Tests\Factory\NavigationVariantFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
class NavigationTest extends ApiTestCase
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

    public function test_create_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-create'],
            ['hyvor_user_id' => 301, 'status' => 'active'],
        );
        LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'is_primary' => true,
        ]);

        $authUser = AuthFake::generateUser(['id' => 301]);
        $this->consoleBlogApi('POST', 'nav-create', '/navigations', [
            'url' => '/header.json',
            'name' => 'Header Nav',
            'type' => 'header',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('/header.json', $json['url']);
        $this->assertSame('header', $json['type']);
        $this->assertCount(1, $json['variants']);
        $this->assertSame('Header Nav', $json['variants'][0]['name']);
    }

    public function test_sort_navigations(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-sort'],
            ['hyvor_user_id' => 302, 'status' => 'active'],
        );
        $nav1 = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'sort' => 0,
        ]);
        $nav2 = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'sort' => 1,
        ]);

        $authUser = AuthFake::generateUser(['id' => 302]);
        $this->consoleBlogApi('PATCH', 'nav-sort', '/navigations/sort', [
            'ids' => [$nav2->getId(), $nav1->getId()],
        ], user: $authUser);

        $this->assertResponseIsSuccessful();
    }

    public function test_update_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-update'],
            ['hyvor_user_id' => 303, 'status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'url' => '/old.json',
            'type' => 'header',
        ]);

        $authUser = AuthFake::generateUser(['id' => 303]);
        $this->consoleBlogApi('PATCH', 'nav-update', '/navigations/' . $nav->getId(), [
            'url' => '/new.json',
            'type' => 'footer',
        ], user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('/new.json', $json['url']);
        $this->assertSame('footer', $json['type']);
    }

    public function test_update_navigation_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-upd-b1'],
            ['hyvor_user_id' => 304, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-upd-b2'],
            ['hyvor_user_id' => 305, 'status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 304]);
        $this->consoleBlogApi('PATCH', 'nav-upd-b1', '/navigations/' . $nav->getId(), [
            'url' => '/hack.json',
            'type' => 'header',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_delete_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-delete'],
            ['hyvor_user_id' => 306, 'status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 306]);
        $this->consoleBlogApi('DELETE', 'nav-delete', '/navigations/' . $nav->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
    }

    public function test_create_navigation_variant(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-create'],
            ['hyvor_user_id' => 307, 'status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'fr',
        ]);

        $authUser = AuthFake::generateUser(['id' => 307]);
        $this->consoleBlogApi('POST', 'nav-var-create', '/navigations/' . $nav->getId() . '/variants', [
            'language_id' => $lang->getId(),
            'name' => 'French Nav',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('French Nav', $json['name']);
        $this->assertSame($lang->getId(), $json['language_id']);
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
