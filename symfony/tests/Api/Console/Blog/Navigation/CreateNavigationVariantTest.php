<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Api\Console\Object\NavigationVariantObject;
use App\Entity\Enum\UserStatus;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use App\Service\Navigation\NavigationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\NavigationFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
#[CoversClass(NavigationService::class)]
#[CoversClass(NavigationVariantObject::class)]
class CreateNavigationVariantTest extends ApiTestCase
{
    public function test_create_navigation_variant(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-create'],
            ['status' => UserStatus::ACTIVE],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
        ]);
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'fr',
        ]);

        $this->consoleBlogApi('POST', 'nav-var-create', '/navigation/' . $nav->getId() . '/variant', [
            'language_id' => $lang->getId(),
            'name' => 'French Nav',
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('French Nav', $json['name']);
        $this->assertSame($lang->getId(), $json['language_id']);
        $this->getEd()->assertDispatched(NavigationVariantChangedEvent::class);
    }

    public function test_language_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-no-lang'],
            ['status' => UserStatus::ACTIVE],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
        ]);

        $this->consoleBlogApi('POST', 'nav-var-no-lang', '/navigation/' . $nav->getId() . '/variant', [
            'language_id' => 99999,
            'name' => 'HB',
        ], user: $user);

        $this->assertResponseFailed(404, 'Language not found');
    }
}
