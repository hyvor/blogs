<?php

namespace App\Tests\Service\Route;

use App\Domains\Route\PermalinkRepository;
use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\BlogHostingAt;
use App\Service\Media\BlogIdEncoder;
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

        $post = PostFactory::createOne(['blog' => $blog]);
        $postVariant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'slug' => 'about',
            'published_at' => new \DateTimeImmutable('2021-12-31 12:00:00'),
        ]);

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
            ],
        );
        $postVariant = PostVariantFactory::createOne([
            'post' => $page,
            'language' => $language,
            'slug' => 'about',
            'published_at' => new \DateTimeImmutable('2021-12-31 12:00:00'),
        ]);

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
            'https://blogs.hyvor.com/media/' . BlogIdEncoder::encode($blog->getId()) . '/image.png',
            $this->getPermalinkService()->getMediaPermalink($media, $blog)
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

    // media ==========

    private function createMedia(Blog $blog, string $name = 'image.png'): \App\Entity\Media
    {
        $media = new \App\Entity\Media();
        $media->setBlog($blog);
        $media->setName($name);
        return $media;
    }

    public function test_media_permalink_does_not_depend_on_blog_hosting(): void
    {
        $blog = BlogFactory::createOne([
            'hosting_at' => BlogHostingAt::SELF,
            'hosting_url' => 'https://example.com/blog',
        ]);
        $encoded = BlogIdEncoder::encode($blog->getId());

        $this->assertSame('https://blogs.hyvor.com/media', $this->getPermalinkService()->getMediaBaseUrl());
        $this->assertSame("https://blogs.hyvor.com/media/$encoded", $this->getPermalinkService()->getMediaBlogBaseUrl($blog));
        $this->assertSame(
            "https://blogs.hyvor.com/media/$encoded/image.png",
            $this->getPermalinkService()->getMediaPermalink($this->createMedia($blog), $blog)
        );
    }

    public function test_media_permalink_with_media_domain(): void
    {
        $this->setEnvVar('MEDIA_DOMAIN', 'media.hyvorblogs.io');
        $blog = BlogFactory::createOne();
        $encoded = BlogIdEncoder::encode($blog->getId());

        $this->assertSame('https://media.hyvorblogs.io', $this->getPermalinkService()->getMediaBaseUrl());
        $this->assertSame(
            "https://media.hyvorblogs.io/$encoded/image.png",
            $this->getPermalinkService()->getMediaPermalink($this->createMedia($blog), $blog)
        );
    }

    public function test_media_permalink_tls_disabled(): void
    {
        $this->setEnvVar('TLS_MODE', 'disabled');
        $blog = BlogFactory::createOne();

        $this->assertSame(
            'http://blogs.hyvor.com/media/' . BlogIdEncoder::encode($blog->getId()) . '/image.png',
            $this->getPermalinkService()->getMediaPermalink($this->createMedia($blog), $blog)
        );
    }

    public function test_get_media_name_from_url(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'supun', 'hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $otherBlog = BlogFactory::createOne();
        $encoded = BlogIdEncoder::encode($blog->getId());
        $service = $this->getPermalinkService();

        // current format
        $this->assertSame('image.png', $service->getMediaNameFromUrl($blog, "https://blogs.hyvor.com/media/$encoded/image.png"));
        $this->assertSame('image.png', $service->getMediaNameFromUrl($blog, "https://blogs.hyvor.com/media/$encoded/image.png/500w"));
        $this->assertSame('image.png', $service->getMediaNameFromUrl($blog, "https://blogs.hyvor.com/media/$encoded/image.png?v=1"));

        // legacy format
        $this->assertSame('image.png', $service->getMediaNameFromUrl($blog, 'https://supun.hyvorblogs.io/media/image.png'));
        $this->assertSame('image.png', $service->getMediaNameFromUrl($blog, 'https://supun.hyvorblogs.io/media/image.png/500w'));
        $this->assertSame('image.png', $service->getMediaNameFromUrl($blog, '/media/image.png'));

        // not a media URL of the blog
        $this->assertNull($service->getMediaNameFromUrl($blog, "https://blogs.hyvor.com/media/$encoded/"));
        $this->assertNull($service->getMediaNameFromUrl($blog, 'https://blogs.hyvor.com/media/' . BlogIdEncoder::encode($otherBlog->getId()) . '/image.png'));
        $this->assertNull($service->getMediaNameFromUrl($blog, 'https://supun.hyvorblogs.io/image.png'));
        $this->assertNull($service->getMediaNameFromUrl($blog, 'https://example.com/media/image.png'));
    }

    // validatePostPermalink ==========

    public function test_validate_post_permalink(): void
    {

        $post = PostFactory::createOne();
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'published_at' => new \DateTimeImmutable('2021-12-31 12:00:00'),
        ]);

        $tag = TagFactory::createOne(['slug' => 'welcome']);
        $post->getTags()->add($tag);

        $author = UserFactory::createOne(['slug' => 'supun']);
        $post->getAuthors()->add($author);

        $this->assertTrue(
            $this->getPermalinkService()->validatePostPermalinkParams($variant, [
                'tag' => 'welcome',
                'author' => 'supun',
                'year' => '2021',
                'month' => '12',
                'day' => '31',
            ])
        );

        $this->assertFalse(
            $this->getPermalinkService()->validatePostPermalinkParams($variant, [
                'tag' => 'wrong-tag',
            ])
        );

        $this->assertFalse(
            $this->getPermalinkService()->validatePostPermalinkParams($variant, [
                'author' => 'wrong-author',
            ])
        );

        $this->assertFalse(
            $this->getPermalinkService()->validatePostPermalinkParams($variant, [
                'year' => '2020',
            ])
        );
    }

}
