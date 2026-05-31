<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Entity\Navigation;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\NavigationFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
class CreateNavigationTest extends ApiTestCase
{
    public function test_create_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-create'],
            ['status' => 'active'],
        );
        LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'is_primary' => true,
        ]);

        $this->consoleBlogApi('POST', 'nav-create', '/navigation', [
            'url' => '/header.json',
            'name' => 'Header Nav',
            'type' => 'header',
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('/header.json', $json['url']);
        $this->assertSame('header', $json['type']);
        $this->assertIsArray($json['variants']);
        $this->assertCount(1, $json['variants']);
        $this->assertIsArray($json['variants'][0]);
        $this->assertSame('Header Nav', $json['variants'][0]['name']);

        $navigations = $this->getEm()->getRepository(Navigation::class)->findAll();
        $this->assertCount(1, $navigations);
        $nav = $navigations[0];
        $this->assertSame('/header.json', $nav->getUrl());
        $this->assertSame('header', $nav->getType());
        $this->assertCount(1, $nav->getVariants());
        $variant = $nav->getVariants()->first();
        $this->assertSame('Header Nav', $variant->getName());
    }

    public function test_fails_when_limit_exceeded(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-create-lim'],
            ['status' => 'active'],
        );
        LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'is_primary' => true,
        ]);

        for ($i = 0; $i < 10; $i++) {
            NavigationFactory::createOne([
                'blog' => $blog,
                'blog_id' => $blog->getId(),
                'type' => 'header',
            ]);
        }

        // 11th navigation should fail
        $this->consoleBlogApi('POST', 'nav-create-lim', '/navigation', [
            'url' => "/nav11.json",
            'name' => "Nav 11",
            'type' => 'header',
        ], user: $user);

        $this->assertResponseFailed(422, 'You have reached the maximum number of navigations for this type (10)');
    }
}
