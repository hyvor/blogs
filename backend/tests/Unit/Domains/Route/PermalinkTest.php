<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Route;

use App\Domains\Route\PermalinkRepository;
use App\Models\PostTag;
use Database\Factories\BlogFactory;
use Database\Factories\LanguageFactory;
use Database\Factories\PostFactory;
use Database\Factories\TagFactory;
use Database\Factories\UserFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Case\DatabaseTestCase;

class PermalinkTest extends DatabaseTestCase
{

    public function testGetsBlogUrl(): void
    {
        $blog = BlogFactory::one(['subdomain' => 'test']);

        config(['blogs.delivery_url' => 'https://hyvorblogs.io']);
        $this->assertSame('https://test.hyvorblogs.io', PermalinkRepository::getBaseUrl($blog));

        config(['blogs.delivery_url' => 'https://localhost:2211']);
        $this->assertSame('https://test.localhost:2211', PermalinkRepository::getBaseUrl($blog));

        config(['blogs.delivery_url' => 'http://userblogs.hyvorstaging.com']);
        $this->assertSame('http://test.userblogs.hyvorstaging.com', PermalinkRepository::getBaseUrl($blog));
    }

    public function testPagePermalinkUsesPageRouteWhenDifferentFromPostRoute(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes(
            ['subdomain' => 'supun'],
            routes: [
                [
                    'name' => 'post',
                    'match' => '/articles/{slug}',
                    'template' => 'post',
                ],
                [
                    'name' => 'page',
                    'match' => '/{slug}',
                    'template' => 'page,post',
                ],
            ]
        );

        $language = $blog->languages[0];
        $this->assertNotNull($language);

        $page = PostFactory::oneFor(
            $blog,
            attr: [
                'is_page' => true,
                'published_at' => '2021-12-31 12:00:00',
            ],
            variantAttr: [
                'slug' => 'about',
            ]
        );

        $this->assertSame(
            'https://supun.hyvorblogs.io/about',
            PermalinkRepository::getPostPermalink(
                $page,
                $blog,
                $language
            )
        );
    }

    // slug
    #[TestWith(['/{slug}', 'https://supun.hyvorblogs.io/about'])]
    // other language
    #[TestWith(['/{slug}', 'https://supun.hyvorblogs.io/fr/about', true])]
    // tag
    #[TestWith(['/{tag}/{slug}', 'https://supun.hyvorblogs.io/welcome/about'])]
    // author
    #[TestWith(['/{author}/{slug}', 'https://supun.hyvorblogs.io/supun/about'])]
    // dates
    #[TestWith(['/{year}/{month}/{day}/{slug}', 'https://supun.hyvorblogs.io/2021/12/31/about'])]
    public function testPostPermalinkWithLanguage(
        string $route,
        string $expect,
        bool $otherLanguage = false
    ): void {
        $blog = BlogFactory::withLanguageAndRoutes(
            ['subdomain' => 'supun'],
            routes: [
                [
                    'name' => 'post',
                    'match' => $route,
                    'template' => 'post',
                ]
            ]
        );
        $language = $blog->languages[0];

        if ($otherLanguage) {
            $language = LanguageFactory::oneFor($blog, attr: [
                'code' => 'fr'
            ]);
            $blog->load('languages'); // to add post variant
        }

        $this->assertNotNull($language);

        $post = PostFactory::oneFor(
            $blog,
            attr: [
                'published_at' => '2021-12-31 12:00:00'
            ],
            variantAttr: [
                'slug' => 'about'
            ]
        );
        $tag = TagFactory::oneFor($blog, attr: [
            'slug' => 'welcome'
        ]);
        TagFactory::postTag($post, $tag);

        $user = UserFactory::oneFor($blog, attr: [
            'slug' => 'supun'
        ]);
        UserFactory::postAuthor($post, $user);

        $this->assertSame(
            $expect,
            PermalinkRepository::getPostPermalink(
                $post,
                $blog,
                $language
            )
        );
    }

}