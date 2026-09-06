<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Entity\Enum\UserStatus;
use App\Entity\Navigation;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\NavigationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\NavigationFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
#[CoversClass(NavigationService::class)]
#[CoversClass(NavigationChangedEvent::class)]
class DeleteNavigationTest extends ApiTestCase
{
    public function test_delete_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-delete'],
            ['status' => UserStatus::ACTIVE],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
        ]);

        $this->consoleBlogApi('DELETE', 'nav-delete', '/navigation/' . $nav->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $navs = $this->getEm()->getRepository(Navigation::class)->findAll();
        $this->assertCount(0, $navs);
        $this->getEd()->assertDispatched(NavigationChangedEvent::class);
    }
}
