<?php

namespace App\Tests\Service\Route;

use App\Domains\Route\PermalinkRepository;
use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\BlogHostingAt;
use App\Service\Route\PermalinkService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(PermalinkService::class)]
class PermalinkServiceTest extends KernelTestCase {

    private function getPermalinkService(): PermalinkService
    {
        return $this->getService(PermalinkService::class);
    }

    // getBlogUrl ============

    public function test_blog_url_subdomain(): void
    {
        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::SUBDOMAIN);
        $blog->setSubdomain('supun');

        $this->assertSame(
            'https://supun.hyvorblogs.io',
            $this->getPermalinkService()->getBlogUrl($blog)
        );
    }

    public function test_blog_url_subdomain_no_delivery_url(): void
    {
        $this->setEnvVar('DELIVERY_URL', '');
        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::SUBDOMAIN);
        $blog->setSubdomain('supun');

        $this->assertSame(
            'https://blogs.hyvor.com/blog/supun',
            $this->getPermalinkService()->getBlogUrl($blog)
        );
    }

    public function test_blog_url_custom_domain(): void
    {
        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::DOMAIN);
        $blog->setCustomDomain(new CustomDomain()->setDomain('supun.io'));

        $this->assertSame(
            'https://supun.io',
            $this->getPermalinkService()->getBlogUrl($blog)
        );
    }

    // custom domain TLS is handled separately (on-demand) and is not affected by TLS_MODE
    public function test_blog_url_custom_domain_ignores_tls_mode(): void
    {
        $this->setEnvVar('TLS_MODE', 'disabled');

        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::DOMAIN);
        $blog->setCustomDomain(new CustomDomain()->setDomain('supun.io'));

        $this->assertSame(
            'https://supun.io',
            $this->getPermalinkService()->getBlogUrl($blog)
        );
    }

    public function test_blog_url_subdomain_no_delivery_url_tls_mode_disabled(): void
    {
        $this->setEnvVar('DELIVERY_URL', '');
        $this->setEnvVar('TLS_MODE', 'disabled');

        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::SUBDOMAIN);
        $blog->setSubdomain('supun');

        $this->assertSame(
            'http://blogs.hyvor.com/blog/supun',
            $this->getPermalinkService()->getBlogUrl($blog)
        );
    }

    public function test_blog_url_self_hosting(): void
    {
        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::SELF);
        $blog->setHostingUrl('https://supun.io');

        $this->assertSame(
            'https://supun.io',
            $this->getPermalinkService()->getBlogUrl($blog)
        );
    }

    // isLinkInBlog ============

    public function test_is_link_in_blog(): void
    {
        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::SUBDOMAIN);
        $blog->setSubdomain('supun');

        $this->assertTrue(
            $this->getPermalinkService()->isLinkInBlog('https://supun.hyvorblogs.io/post/1', $blog)
        );

        $this->assertFalse(
            $this->getPermalinkService()->isLinkInBlog('https://other.hyvorblogs.io/post/1', $blog)
        );
    }

    // getBlogUrlWithPath ============

    public function test_blog_url_with_path(): void
    {
        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::SUBDOMAIN);
        $blog->setSubdomain('supun');

        $this->assertSame(
            'https://supun.hyvorblogs.io/post/1',
            $this->getPermalinkService()->getBlogUrlWithPath($blog, '/post/1')
        );

        $this->assertSame(
            'https://supun.hyvorblogs.io/post/1',
            $this->getPermalinkService()->getBlogUrlWithPath($blog, 'post/1')
        );

        $this->assertSame(
            'https://supun.hyvorblogs.io',
            $this->getPermalinkService()->getBlogUrlWithPath($blog, '')
        );

        $this->assertSame(
            'https://supun.hyvorblogs.io',
            $this->getPermalinkService()->getBlogUrlWithPath($blog, '/')
        );
    }

    // getBlogPermalink ==========

    public function test_blog_permalink_primary_language(): void
    {
        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::SUBDOMAIN);
        $blog->setSubdomain('supun');

        $language = new \App\Entity\Language();
        $language->setIsPrimary(true);
        $language->setCode('en');

        $this->assertSame(
            'https://supun.hyvorblogs.io',
            $this->getPermalinkService()->getBlogPermalink($blog, $language)
        );
    }

    public function test_blog_permalink_non_primary_language(): void
    {
        $blog = new Blog();
        $blog->setHostingAt(BlogHostingAt::SUBDOMAIN);
        $blog->setSubdomain('supun');

        $language = new \App\Entity\Language();
        $language->setIsPrimary(false);
        $language->setCode('fr');

        $this->assertSame(
            'https://supun.hyvorblogs.io/fr',
            $this->getPermalinkService()->getBlogPermalink($blog, $language)
        );
    }

    // getPostVariantPermalink =========

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
    public function test_post_variant_permalink(
        string $route,
        string $expect,
        bool $otherLanguage = false
    ): void {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(
            [
                'subdomain' => 'supun',
                'hosting_at' => BlogHostingAt::SUBDOMAIN
            ],
            routes: [
                [
                    'name' => 'post',
                    'match' => $route,
                    'template' => 'post',
                ]
            ]
        );
        $language = $blog->getLanguages()[0];

        if ($otherLanguage) {
            $language = LanguageFactory::createOneFor($blog, attributes: [
                'code' => 'fr'
            ]);
        }

        $this->assertNotNull($language);

        $post = PostFactory::createOne(
            [
                'blog' => $blog,
                'published_at' => new \DateTimeImmutable('2021-12-31 12:00:00')
            ]
        );
        $postVariant = PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'slug' => 'about']);

        $tag = TagFactory::createOne([
            'blog' => $blog,
            'slug' => 'welcome'
        ]);
        $post->getTags()->add($tag);

        $user = UserFactory::createOne([
            'blog' => $blog,
            'slug' => 'supun'
        ]);
        $post->getAuthors()->add($user);

        $this->assertSame(
            $expect,
            $this->getPermalinkService()->getPostVariantPermalink($postVariant),
        );
    }

    public function test_post_variant_permalink_uses_page_route_for_pages(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(
            ['subdomain' => 'supun', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
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

        $language = $blog->getLanguages()[0];
        $this->assertNotNull($language);

        $page = PostFactory::createOne(
            [
                'blog' => $blog,
                'is_page' => true,
                'published_at' => new \DateTimeImmutable('2021-12-31 12:00:00'),
            ],
        );
        $postVariant = PostVariantFactory::createOne(['post' => $page, 'language' => $language, 'slug' => 'about']);

        $this->assertSame(
            'https://supun.hyvorblogs.io/about',
            $this->getPermalinkService()->getPostVariantPermalink($postVariant)
        );
    }

    // getTagPermalink =========

    public function test_get_tag_permalink_primary_language(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(
            ['subdomain' => 'supun', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            routes: [
                [
                    'name' => 'tag',
                    'match' => '/tag/{slug}',
                    'template' => 'tag',
                ],
            ]
        );

        $language = $blog->getLanguages()[0];
        $this->assertNotNull($language);

        $tag = TagFactory::createOne([
            'blog' => $blog,
            'slug' => 'welcome'
        ]);

        $this->assertSame(
            'https://supun.hyvorblogs.io/tag/welcome',
            $this->getPermalinkService()->getTagPermalink($tag, $blog, $language)
        );

        $this->assertSame(
            '/tag/welcome',
            $this->getPermalinkService()->getTagPermalink($tag, $blog, $language, onlyPath: true)
        );
    }

    public function test_get_tag_permalink_non_primary_language(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(
            ['subdomain' => 'supun', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            routes: [
                [
                    'name' => 'tag',
                    'match' => '/tag/{slug}',
                    'template' => 'tag',
                ],
            ]
        );

        $language = LanguageFactory::createOneFor($blog, attributes: [
            'code' => 'fr'
        ]);

        $tag = TagFactory::createOne([
            'blog' => $blog,
            'slug' => 'welcome'
        ]);

        $this->assertSame(
            'https://supun.hyvorblogs.io/fr/tag/welcome',
            $this->getPermalinkService()->getTagPermalink($tag, $blog, $language)
        );

        $this->assertSame(
            '/fr/tag/welcome',
            $this->getPermalinkService()->getTagPermalink($tag, $blog, $language, onlyPath: true)
        );
    }

    // getAuthorPermalink =========

    public function test_get_author_permalink_primary_language(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(
            ['subdomain' => 'supun', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            routes: [
                [
                    'name' => 'author',
                    'match' => '/author/{slug}',
                    'template' => 'author',
                ],
            ]
        );

        $language = $blog->getLanguages()[0];
        $this->assertNotNull($language);

        $author = UserFactory::createOne([
            'blog' => $blog,
            'slug' => 'supun'
        ]);

        $this->assertSame(
            'https://supun.hyvorblogs.io/author/supun',
            $this->getPermalinkService()->getAuthorPermalink($author, $blog, $language)
        );

        $this->assertSame(
            '/author/supun',
            $this->getPermalinkService()->getAuthorPermalink($author, $blog, $language, onlyPath: true)
        );
    }

    public function test_get_author_permalink_non_primary_language(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(
            ['subdomain' => 'supun', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            routes: [
                [
                    'name' => 'author',
                    'match' => '/author/{slug}',
                    'template' => 'author',
                ],
            ]
        );

        $language = LanguageFactory::createOneFor($blog, attributes: [
            'code' => 'fr'
        ]);

        $author = UserFactory::createOne([
            'blog' => $blog,
            'slug' => 'supun'
        ]);

        $this->assertSame(
            'https://supun.hyvorblogs.io/fr/author/supun',
            $this->getPermalinkService()->getAuthorPermalink($author, $blog, $language)
        );

        $this->assertSame(
            '/fr/author/supun',
            $this->getPermalinkService()->getAuthorPermalink($author, $blog, $language, onlyPath: true)
        );
    }

    // other non-dynamic methods
    public function test_get_media_asserts_permalinks(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(
            ['subdomain' => 'supun', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            routes: []
        );

        $media = new \App\Entity\Media();
        $media->setBlog($blog);
        $media->setName('image.png');

        $this->assertSame(
            'https://supun.hyvorblogs.io/media/image.png',
            $this->getPermalinkService()->getMediaPermalink($media, $blog)
        );

        $this->assertSame(
            '/media/image.png',
            $this->getPermalinkService()->getMediaPermalink($media, $blog, onlyPath: true)
        );

        $this->assertSame(
            'https://supun.hyvorblogs.io/assets/image.png',
            $this->getPermalinkService()->getAssetPermalink('image.png', $blog)
        );

        $this->assertSame(
            '/assets/image.png',
            $this->getPermalinkService()->getAssetPermalink('image.png', $blog, onlyPath: true)
        );
    }

    // validatePostPermalink ==========

    public function test_validate_post_permalink(): void
    {

        $post = PostFactory::createOne();
        $post->setPublishedAt(new \DateTimeImmutable('2021-12-31 12:00:00'));

        $tag = TagFactory::createOne(['slug' => 'welcome']);
        $post->getTags()->add($tag);

        $author = UserFactory::createOne(['slug' => 'supun']);
        $post->getAuthors()->add($author);

        $this->assertTrue(
            $this->getPermalinkService()->validatePostPermalinkParams($post, [
                'tag' => 'welcome',
                'author' => 'supun',
                'year' => '2021',
                'month' => '12',
                'day' => '31',
            ])
        );

        $this->assertFalse(
            $this->getPermalinkService()->validatePostPermalinkParams($post, [
                'tag' => 'wrong-tag',
            ])
        );

        $this->assertFalse(
            $this->getPermalinkService()->validatePostPermalinkParams($post, [
                'author' => 'wrong-author',
            ])
        );

        $this->assertFalse(
            $this->getPermalinkService()->validatePostPermalinkParams($post, [
                'year' => '2020',
            ])
        );
    }

}
