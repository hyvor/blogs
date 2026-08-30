<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Api\Console\Object\NavigationObject;
use App\Entity\Enum\NavigationType;
use App\Entity\Enum\UserStatus;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\NavigationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\NavigationFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
#[CoversClass(NavigationObject::class)]
#[CoversClass(NavigationService::class)]
class UpdateNavigationTest extends ApiTestCase
{
    public function test_update_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-update'],
            ['status' => UserStatus::ACTIVE],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'url' => '/old.json',
            'type' => NavigationType::HEADER,
        ]);

        $this->consoleBlogApi('PATCH', 'nav-update', '/navigation/' . $nav->getId(), [
            'url' => '/new.json',
            'type' => 'footer',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('/new.json', $json['url']);
        $this->assertSame('footer', $json['type']);
        $this->getEd()->assertDispatched(NavigationChangedEvent::class);
    }

    public function test_update_navigation_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-upd-b1'],
            ['status' => UserStatus::ACTIVE],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-upd-b2'],
            ['status' => UserStatus::ACTIVE],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog2,
        ]);

        $this->consoleBlogApi('PATCH', 'nav-upd-b1', '/navigation/' . $nav->getId(), [
            'url' => '/hack.json',
            'type' => 'header',
        ], user: $user1);

        $this->assertResponseStatusCodeSame(404);
    }
}
