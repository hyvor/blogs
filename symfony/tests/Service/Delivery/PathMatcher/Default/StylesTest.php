<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default;

use App\Entity\Enum\BlogType;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\Dto\CacheControl;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\StylesProcessor;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(PathMatcher::class)]
#[CoversClass(StylesProcessor::class)]
class StylesTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getService(CacheItemPoolInterface::class)->clear();
    }

    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    /**
     * @param array<string, mixed> $blogAttrs
     */
    private function createBlogWithScss(array $blogAttrs = [], string $scss = 'body {color: red;}'): \App\Entity\Blog
    {
        $blog = BlogFactory::createOne($blogAttrs);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => \App\Entity\Enum\ThemeFileFolder::STYLES,
            'name' => 'index.scss',
            'content' => $scss,
        ]);
        return $blog;
    }

    public function test_matches_styles_css(): void
    {
        $blog = $this->createBlogWithScss();

        $response = $this->pathMatcher()->match($blog, '/styles.css');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(DeliveryFileType::ASSET, $response->fileType);
        $this->assertSame('text/css', $response->mimeType);
        $this->assertSame(CacheControl::ONE_YEAR, $response->cacheControl);
    }

    public function test_works_with_imports(): void
    {
        $blog = BlogFactory::createOne();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => \App\Entity\Enum\ThemeFileFolder::STYLES,
            'name' => 'index.scss',
            'content' => '@import "imported.scss";',
        ]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => \App\Entity\Enum\ThemeFileFolder::STYLES,
            'name' => 'imported.scss',
            'content' => 'body {color: red;}',
        ]);

        $response = $this->pathMatcher()->match($blog, '/styles.css');

        $this->assertSame(200, $response->status);
        $this->assertSame('body{color:red}', $response->content);
    }

    public function test_shows_error_on_invalid_scss(): void
    {
        $blog = $this->createBlogWithScss(scss: '{');

        $response = $this->pathMatcher()->match($blog, '/styles.css');

        $this->assertSame(500, $response->status);
        $this->assertStringContainsString('SCSS Error', (string)$response->content);
    }

    public function test_no_cache_for_dev(): void
    {
        $blog = $this->createBlogWithScss(['type' => BlogType::DEV]);

        $response = $this->pathMatcher()->match($blog, '/styles.css');

        $this->assertSame(CacheControl::NO_CACHE, $response->cacheControl);
    }

    public function test_adds_bunny_font_css(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('body{font-family:Roboto}'),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);
        $this->getService(CacheItemPoolInterface::class)->clear();

        $blog = $this->createBlogWithScss();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => null,
            'name' => 'config.yaml',
            'content' => "THEME_FONTS: roboto:400,600",
        ]);

        $response = $this->pathMatcher()->match($blog, '/styles.css');

        $this->assertSame(200, $response->status);
        $this->assertSame("body{color:red}body{font-family:Roboto}", $response->content);
    }

    public function test_adds_comment_when_bunny_fails_and_sets_small_cache(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 500]),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);
        $this->getService(CacheItemPoolInterface::class)->clear();

        $blog = $this->createBlogWithScss();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => null,
            'name' => 'config.yaml',
            'content' => "THEME_FONTS: roboto:400,600",
        ]);

        $response = $this->pathMatcher()->match($blog, '/styles.css');

        $this->assertSame(200, $response->status);
        $this->assertNotNull($response->content);
        $this->assertStringContainsString("/* Unable to fetch fonts from Bunny: Request failed */", $response->content);
        $this->assertSame(CacheControl::ONE_HOUR, $response->cacheControl);
    }
}
