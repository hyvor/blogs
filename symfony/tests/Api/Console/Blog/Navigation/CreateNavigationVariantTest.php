<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\NavigationFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
class CreateNavigationVariantTest extends ApiTestCase
{
    public function test_create_navigation_variant(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-var-create'],
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

        $this->consoleBlogApi('POST', 'nav-var-create', '/navigation/' . $nav->getId() . '/variant', [
            'language_id' => $lang->getId(),
            'name' => 'French Nav',
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('French Nav', $json['name']);
        $this->assertSame($lang->getId(), $json['language_id']);
    }
}
