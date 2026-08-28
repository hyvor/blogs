<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content;

use App\Entity\Blog;
use App\Service\Blog\UpdateBlogUrls\Updater\HostingUpdater;
use App\Service\Blog\UpdateBlogUrls\Updater\MediaUpdater;
use App\Service\Post\Content\DocUrlUpdater;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DocUrlUpdater::class)]
class UrlUpdaterTest extends KernelTestCase
{
    private const array DOC = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'image',
                'attrs' => ['src' => 'https://old.com/media/image.jpg'],
            ],
            [
                'type' => 'audio',
                'attrs' => ['src' => 'https://old.com/media/audio.mp3'],
            ],
            [
                'type' => 'text',
                'text' => 'test',
                'marks' => [
                    [
                        'type' => 'link',
                        'attrs' => ['href' => 'https://old.com/page'],
                    ],
                ],
            ],
        ],
    ];

    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    /**
     * @param list<int|string> $path
     */
    private function dig(mixed $data, array $path): mixed
    {
        foreach ($path as $key) {
            $this->assertIsArray($data);
            $data = $data[$key];
        }
        return $data;
    }

    public function test_updates_from_old_to_new(): void
    {
        $doc = $this->service()->getDocumentFromJson(self::DOC, $this->blog());

        $updater = new DocUrlUpdater($doc);
        $updated = $updater->updateFromOldToNew('https://old.com', 'https://new.com');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => 'https://new.com/media/image.jpg',
                        'alt' => null,
                        'width' => null,
                        'height' => null,
                        'suggestions' => null,
                    ],
                ],
                [
                    'type' => 'audio',
                    'attrs' => [
                        'src' => 'https://new.com/media/audio.mp3',
                        'suggestions' => null,
                    ],
                ],
                [
                    'type' => 'text',
                    'text' => 'test',
                    'marks' => [
                        [
                            'type' => 'link',
                            'attrs' => ['href' => 'https://new.com/page'],
                        ],
                    ],
                ],
            ],
        ], json_decode($updated->toJson(), true));
    }

    public function test_updates_from_old_to_new_ignores_non_matching_urls(): void
    {
        $doc = $this->service()->getDocumentFromJson([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => ['src' => 'https://other.com/media/image.jpg'],
                ],
                [
                    'type' => 'text',
                    'text' => 'test',
                    'marks' => [
                        [
                            'type' => 'link',
                            'attrs' => ['href' => 'https://other.com/page'],
                        ],
                    ],
                ],
            ],
        ], $this->blog());

        $updater = new DocUrlUpdater($doc);
        $updated = $updater->updateFromOldToNew('https://old.com', 'https://new.com');

        $decoded = json_decode($updated->toJson(), true);
        $this->assertSame('https://other.com/media/image.jpg', $this->dig($decoded, ['content', 0, 'attrs', 'src']));
        $this->assertSame('https://other.com/page', $this->dig($decoded, ['content', 1, 'marks', 0, 'attrs', 'href']));
    }

    public function test_updates_from_old_to_new_with_media_disabled(): void
    {
        $doc = $this->service()->getDocumentFromJson(self::DOC, $this->blog());

        $updater = new DocUrlUpdater($doc);
        $updated = $updater->updateFromOldToNew('https://old.com', 'https://new.com', updateMedia: false);

        $decoded = json_decode($updated->toJson(), true);
        $this->assertSame('https://old.com/media/image.jpg', $this->dig($decoded, ['content', 0, 'attrs', 'src']));
        $this->assertSame('https://old.com/media/audio.mp3', $this->dig($decoded, ['content', 1, 'attrs', 'src']));
        $this->assertSame('https://new.com/page', $this->dig($decoded, ['content', 2, 'marks', 0, 'attrs', 'href']));
    }

    public function test_updates_from_old_to_new_with_links_disabled(): void
    {
        $doc = $this->service()->getDocumentFromJson(self::DOC, $this->blog());

        $updater = new DocUrlUpdater($doc);
        $updated = $updater->updateFromOldToNew('https://old.com', 'https://new.com', updateLinks: false);

        $decoded = json_decode($updated->toJson(), true);
        $this->assertSame('https://new.com/media/image.jpg', $this->dig($decoded, ['content', 0, 'attrs', 'src']));
        $this->assertSame('https://new.com/media/audio.mp3', $this->dig($decoded, ['content', 1, 'attrs', 'src']));
        $this->assertSame('https://old.com/page', $this->dig($decoded, ['content', 2, 'marks', 0, 'attrs', 'href']));
    }

    public function test_updates_from_updater_with_hosting_updater(): void
    {
        // HostingUpdater matches by URL prefix only - unlike updateFromOldToNew,
        // it has no "/media/" requirement, so any URL under the old host is rewritten
        $doc = $this->service()->getDocumentFromJson([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => ['src' => 'https://old.com/uploads/image.jpg'],
                ],
                [
                    'type' => 'text',
                    'text' => 'test',
                    'marks' => [
                        [
                            'type' => 'link',
                            'attrs' => ['href' => 'https://old.com/page'],
                        ],
                    ],
                ],
            ],
        ], $this->blog());

        $updater = new DocUrlUpdater($doc);
        $updated = $updater->updateFromUpdater(new HostingUpdater('https://old.com', 'https://new.com'));

        $decoded = json_decode($updated->toJson(), true);
        $this->assertSame('https://new.com/uploads/image.jpg', $this->dig($decoded, ['content', 0, 'attrs', 'src']));
        $this->assertSame('https://new.com/page', $this->dig($decoded, ['content', 1, 'marks', 0, 'attrs', 'href']));
    }

    public function test_updates_from_updater_with_media_updater(): void
    {
        // MediaUpdater matches by exact URL only
        $doc = $this->service()->getDocumentFromJson([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => ['src' => 'https://old.com/media/image.jpg'],
                ],
                [
                    'type' => 'image',
                    'attrs' => ['src' => 'https://old.com/media/other.jpg'],
                ],
            ],
        ], $this->blog());

        $updater = new DocUrlUpdater($doc);
        $updated = $updater->updateFromUpdater(
            new MediaUpdater('https://old.com/media/image.jpg', 'https://new.com/media/image.jpg')
        );

        $decoded = json_decode($updated->toJson(), true);
        $this->assertSame('https://new.com/media/image.jpg', $this->dig($decoded, ['content', 0, 'attrs', 'src']));
        $this->assertSame('https://old.com/media/other.jpg', $this->dig($decoded, ['content', 1, 'attrs', 'src']));
    }

    public function test_updates_from_updater_with_media_disabled(): void
    {
        $doc = $this->service()->getDocumentFromJson(self::DOC, $this->blog());

        $updater = new DocUrlUpdater($doc);
        $updated = $updater->updateFromUpdater(
            new HostingUpdater('https://old.com', 'https://new.com'),
            updateMedia: false
        );

        $decoded = json_decode($updated->toJson(), true);
        $this->assertSame('https://old.com/media/image.jpg', $this->dig($decoded, ['content', 0, 'attrs', 'src']));
        $this->assertSame('https://old.com/media/audio.mp3', $this->dig($decoded, ['content', 1, 'attrs', 'src']));
        $this->assertSame('https://new.com/page', $this->dig($decoded, ['content', 2, 'marks', 0, 'attrs', 'href']));
    }

    public function test_updates_from_updater_with_links_disabled(): void
    {
        $doc = $this->service()->getDocumentFromJson(self::DOC, $this->blog());

        $updater = new DocUrlUpdater($doc);
        $updated = $updater->updateFromUpdater(
            new HostingUpdater('https://old.com', 'https://new.com'),
            updateLinks: false
        );

        $decoded = json_decode($updated->toJson(), true);
        $this->assertSame('https://new.com/media/image.jpg', $this->dig($decoded, ['content', 0, 'attrs', 'src']));
        $this->assertSame('https://new.com/media/audio.mp3', $this->dig($decoded, ['content', 1, 'attrs', 'src']));
        $this->assertSame('https://old.com/page', $this->dig($decoded, ['content', 2, 'marks', 0, 'attrs', 'href']));
    }

    public function test_updates_nested_content(): void
    {
        $doc = $this->service()->getDocumentFromJson([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => ['src' => 'https://old.com/media/image.jpg'],
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'visit',
                            'marks' => [
                                [
                                    'type' => 'link',
                                    'attrs' => ['href' => 'https://old.com/page'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $this->blog());

        $updater = new DocUrlUpdater($doc);
        $updated = $updater->updateFromOldToNew('https://old.com', 'https://new.com');

        $decoded = json_decode($updated->toJson(), true);
        $this->assertSame('https://new.com/media/image.jpg', $this->dig($decoded, ['content', 0, 'content', 0, 'attrs', 'src']));
        $this->assertSame('https://new.com/page', $this->dig($decoded, ['content', 1, 'content', 0, 'marks', 0, 'attrs', 'href']));
    }
}
