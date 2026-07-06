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
use App\Tests\Factory\NavigationVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
#[CoversClass(NavigationService::class)]
#[CoversClass(NavigationVariantObject::class)]
class UpdateNavigationVariantTest extends ApiTestCase
{
    public function test_update_navigation_variant(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-update'],
            ['status' => UserStatus::ACTIVE],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
        ]);
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'en',
        ]);
        NavigationVariantFactory::createOne([
            'navigation' => $nav,
            'language' => $lang,
            'name' => 'Old Name',
        ]);

        $this->consoleBlogApi('PATCH', 'nav-var-update', '/navigation/' . $nav->getId() . '/variant', [
            'language_id' => $lang->getId(),
            'name' => 'New Name',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('New Name', $json['name']);
        $this->assertSame($lang->getId(), $json['language_id']);
        $this->getEd()->assertDispatched(NavigationVariantChangedEvent::class);
    }

    public function test_variant_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-upd-nf'],
            ['status' => UserStatus::ACTIVE],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
        ]);
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'fr',
        ]);

        $this->consoleBlogApi('PATCH', 'nav-var-upd-nf', '/navigation/' . $nav->getId() . '/variant', [
            'language_id' => $lang->getId(),
            'name' => 'Nonexistent',
        ], user: $user);

        $this->assertResponseFailed(404, 'Navigation variant not found');
    }

    public function test_language_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-upd-nl'],
            ['status' => UserStatus::ACTIVE],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
        ]);

        $this->consoleBlogApi('PATCH', 'nav-var-upd-nl', '/navigation/' . $nav->getId() . '/variant', [
            'language_id' => 99999,
            'name' => 'Ghost',
        ], user: $user);

        $this->assertResponseFailed(404, 'Language not found');
    }
}
