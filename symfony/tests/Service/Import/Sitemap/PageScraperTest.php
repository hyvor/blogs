<?php

namespace App\Tests\Service\Import\Sitemap;

use App\Service\Import\Sitemap\PageScraper\PageScraper;
use App\Service\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Service\Post\Content\PostContentService;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(PageScraper::class)]
class PageScraperTest extends KernelTestCase
{
    private function scraper(string $html, PageScraperOptions $options): PageScraper
    {
        static::getContainer()->set(HttpClientInterface::class, new MockHttpClient(new MockResponse($html)));

        $blog = BlogFactory::createOne();

        return new PageScraper(
            $blog,
            'https://example.com/page',
            $options,
            $this->getService(HttpClientInterface::class),
            'TestBot/1.0'
        );
    }

    public function test_scrapes_title_description_and_content(): void
    {
        $scraper = $this->scraper(<<<HTML
            <html>
                <body>
                    <h1>This is the <b>title</b></h1>
                    <p class="description">
                        So the <i>description</i>
                    </p>
                    <article>
                        <p>Hello World</p>
                    </article>
                </body>
            </html>
            HTML, new PageScraperOptions(
            titleSelector: 'h1',
            descriptionSelector: 'p.description',
            contentSelector: 'article',
        ));
        $scraper->scrape();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello World'],
                    ],
                ],
            ],
        ]), $scraper->content);
        $this->assertSame('This is the title', $scraper->title);
        $this->assertSame('So the description', $scraper->description);
    }

    public function test_scrapes_excludes_elements_from_content(): void
    {
        $scraper = $this->scraper(<<<HTML
            <article>
                <p>Hello World</p>
                <blockquote>
                    This is an ad
                </blockquote>
            </article>
            HTML, new PageScraperOptions(
            titleSelector: 'h1',
            descriptionSelector: 'p.description',
            contentSelector: 'article',
            contentExcludeSelector: 'blockquote',
        ));
        $scraper->scrape();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello World'],
                    ],
                ],
            ],
        ]), $scraper->content);
    }

    public function test_converts_elements_within_codeblocks_to_text(): void
    {
        $scraper = $this->scraper(<<<HTML
            <article>
                <pre><code><span>Test</span><b> code block</b>
            <span>in new line</span></code></pre>
            </article>
            HTML, new PageScraperOptions(
            titleSelector: 'h1',
            descriptionSelector: 'p.description',
            contentSelector: 'article',
            contentExcludeSelector: 'blockquote',
        ));
        $scraper->scrape();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'code_block',
                    'attrs' => [
                        'language' => '',
                        'name' => '',
                        'annotations' => '',
                    ],
                    'content' => [
                        ['type' => 'text', 'text' => "Test code block\nin new line"],
                    ],
                ],
            ],
        ]), $scraper->content);
    }

    public function test_converts_iframes_to_embeds(): void
    {
        $scraper = $this->scraper(<<<HTML
            <article>
                <iframe src="https://www.youtube.com/embed/1234"></iframe>
            </article>
            HTML, new PageScraperOptions(contentSelector: 'article'));
        $scraper->scrape();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'embed',
                            'attrs' => [
                                'url' => 'https://www.youtube.com/embed/1234',
                            ],
                        ],
                    ],
                ],
            ],
        ]), $scraper->content);
    }

    public function test_converts_p_img_to_just_img(): void
    {
        $scraper = $this->scraper(<<<HTML
            <article>
                <p>
                    <img src="https://example.com/image.jpg" />
                </p>
            </article>
            HTML, new PageScraperOptions(contentSelector: 'article'));
        $scraper->scrape();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'https://example.com/image.jpg',
                                'alt' => null,
                                'width' => null,
                                'height' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ]), $scraper->content);
    }

    public function test_prepends_when_p_img_has_other_elements(): void
    {
        $scraper = $this->scraper(<<<HTML
            <article>
                <p>Hello World<img src="https://example.com/image.jpg" /></p>
            </article>
            HTML, new PageScraperOptions(contentSelector: 'article'));
        $scraper->scrape();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'https://example.com/image.jpg',
                                'alt' => null,
                                'width' => null,
                                'height' => null,
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello World',
                        ],
                    ],
                ],
            ],
        ]), $scraper->content);
    }
}
