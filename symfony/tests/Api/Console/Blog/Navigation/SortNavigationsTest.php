<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Api\Console\Object\NavigationObject;
use App\Entity\Enum\UserStatus;
use App\Entity\Navigation;
use App\Service\Navigation\NavigationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\NavigationFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
#[CoversClass(NavigationObject::class)]
#[CoversClass(NavigationService::class)]
class SortNavigationsTest extends ApiTestCase
{
    public function test_sort_navigations(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-sort'],
            ['status' => UserStatus::ACTIVE],
        );
        $nav1 = NavigationFactory::createOne([
            'blog' => $blog,
            'sort' => 0,
        ]);
        $nav2 = NavigationFactory::createOne([
            'blog' => $blog,
            'sort' => 1,
        ]);

        $this->consoleBlogApi('PATCH', 'nav-sort', '/navigations/sort', [
            'ids' => [$nav2->getId(), $nav1->getId()],
        ], user: $user);

        $this->assertResponseIsSuccessful();

        $navs = $this->getEm()->getRepository(Navigation::class)->findBy([], ['sort' => 'ASC']);
        $this->assertCount(2, $navs);
        $this->assertSame($nav2->getId(), $navs[0]->getId());
        $this->assertSame($nav1->getId(), $navs[1]->getId());
    }
}
