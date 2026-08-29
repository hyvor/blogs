<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes\Bookmark;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Post\Content\Nodes\Bookmark\Bookmark;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use App\Service\Theme\ThemeFilesService;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(Bookmark::class)]
class BookmarkTest extends KernelTestCase
{
    private const string URL = 'https://example.com';
    private const string TITLE = 'I am title';
    private const string DESCRIPTION = 'I am description';

    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function postSchema(): PostSchema
    {
        return $this->getService(PostSchema::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    /**
     * @param list<int|string> $path
     */
    private function digOrNull(mixed $data, array $path): mixed
    {
        foreach ($path as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }

    private function mockLinkResponse(): void
    {
        $html = <<<HTML
            <html>
            <head>
                <title>{self::TITLE}</title>
                <meta name="description" content="{self::DESCRIPTION}">
                <meta name="og:image" content="https://example.com/thumb.png">
                <link rel="icon" href="https://example.com/favicon.ico">
                <link rel="canonical" href="{self::URL}">
            </head>
            </html>
            HTML;
        $html = str_replace(
            ['{self::TITLE}', '{self::DESCRIPTION}', '{self::URL}'],
            [self::TITLE, self::DESCRIPTION, self::URL],
            $html
        );

        $mockClient = new MockHttpClient(new MockResponse($html));
        static::getContainer()->set(HttpClientInterface::class, $mockClient);
    }

    public function test_json_to_html(): void
    {
        $this->mockLinkResponse();

        $json = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bookmark',
                    'attrs' => ['url' => self::URL],
                ],
            ],
        ];

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertStringContainsString(self::URL, $html);
        $this->assertStringContainsString(self::TITLE, $html);
        $this->assertStringContainsString(self::DESCRIPTION, $html);
    }

    public function test_html_to_json(): void
    {
        $html = '<a class="bookmark" data-url="https://talk.hyvor.com"></a>';
        $json = $this->postSchema()->documentFromHtml($html)->toJson();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bookmark',
                    'attrs' => ['url' => 'https://talk.hyvor.com', 'suggestions' => null],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }

    public function test_ignores_a_tag_without_bookmark_class(): void
    {
        $html = '<a href="https://talk.hyvor.com">link text</a>';
        $json = $this->postSchema()->documentFromHtml($html)->toJson();

        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        $this->assertNotSame('bookmark', $this->digOrNull($decoded, ['content', 0, 'content', 0, 'marks', 0, 'type']));
    }

    public function test_custom_template(): void
    {
        $this->mockLinkResponse();

        $blog = BlogFactory::createOne();
        $this->getService(ThemeFilesService::class)->createOrUpdateFile(
            $blog,
            ThemeFileFolder::TEMPLATES,
            'node-bookmark.twig',
            '<a class="custom-bookmark">{{ data.url }} | {{ data.title }} | {{ data.description }}</a>'
        );

        $json = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bookmark',
                    'attrs' => ['url' => self::URL],
                ],
            ],
        ];

        $html = $this->service()->getHtml($json, $blog);

        $this->assertStringContainsString('custom-bookmark', $html);
        $this->assertStringContainsString(self::URL, $html);
        $this->assertStringContainsString(self::TITLE, $html);
        $this->assertStringContainsString(self::DESCRIPTION, $html);
    }
}
