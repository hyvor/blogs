<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Entity\NavigationVariant;
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
class DeleteNavigationVariantTest extends ApiTestCase
{
    public function test_delete_navigation_variant(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-delete'],
            ['status' => 'active'],
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
        $variant = NavigationVariantFactory::createOne([
            'navigation' => $nav,
            'language' => $lang,
            'language_id' => $lang->getId(),
        ]);
        $variantId = $variant->getId();

        $this->consoleBlogApi('DELETE', 'nav-var-delete', '/navigation/' . $nav->getId() . '/variant', [
            'language_id' => $lang->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertNull(
            $this->getEm()->getRepository(NavigationVariant::class)->find($variantId)
        );
        $this->getEd()->assertDispatched(NavigationVariantChangedEvent::class);
    }

    public function test_variant_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-del-nf'],
            ['status' => 'active'],
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

        $this->consoleBlogApi('DELETE', 'nav-var-del-nf', '/navigation/' . $nav->getId() . '/variant', [
            'language_id' => $lang->getId(),
        ], user: $user);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_language_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-del-nl'],
            ['status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);

        $this->consoleBlogApi('DELETE', 'nav-var-del-nl', '/navigation/' . $nav->getId() . '/variant', [
            'language_id' => 99999,
        ], user: $user);

        $this->assertResponseStatusCodeSame(404);
    }
}
