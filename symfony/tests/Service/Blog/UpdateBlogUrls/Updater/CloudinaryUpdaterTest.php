<?php

namespace App\Tests\Service\Blog\UpdateBlogUrls\Updater;

use App\Service\Blog\UpdateBlogUrls\Updater\CloudinaryUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CloudinaryUpdater::class)]
class CloudinaryUpdaterTest extends TestCase
{

    /**
     * @return iterable<string, array{string, false|string}>
     */
    public static function urls(): iterable
    {
        yield 'video' => [
            'https://res.cloudinary.com/dqabfne6s/video/upload/v1726698292/blogs.hyvor.com/dream-big.mp3',
            'https://media.hyvor.com/blogs.hyvor.com/dream-big.mp3',
        ];
        yield 'image' => [
            'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images/post-featured-images/1.webp',
            'https://media.hyvor.com/blogs.hyvor.com/filler-images/post-featured-images/1.webp',
        ];
        yield 'no version' => [
            'https://res.cloudinary.com/dqabfne6s/image/upload/blogs.hyvor.com/a.png',
            'https://media.hyvor.com/blogs.hyvor.com/a.png',
        ];
        yield 'other cloudinary account' => [
            'https://res.cloudinary.com/other/image/upload/v1/a.png',
            false,
        ];
        yield 'other host' => ['https://example.com/a.png', false];
        yield 'already migrated' => ['https://media.hyvor.com/blogs.hyvor.com/a.png', false];
    }

    #[DataProvider('urls')]
    public function test_update(string $url, false|string $expected): void
    {
        $this->assertSame($expected, new CloudinaryUpdater()->update($url));
    }

    public function test_counts_updates(): void
    {
        $updater = new CloudinaryUpdater();
        $updater->update('https://res.cloudinary.com/dqabfne6s/image/upload/v1/a.png');
        $updater->update('https://example.com/a.png');
        $updater->update('https://res.cloudinary.com/dqabfne6s/image/upload/v1/b.png');
        $this->assertSame(2, $updater->getUpdatedCount());
        $this->assertSame([
            'https://res.cloudinary.com/dqabfne6s/image/upload/v1/a.png' => 'https://media.hyvor.com/a.png',
            'https://res.cloudinary.com/dqabfne6s/image/upload/v1/b.png' => 'https://media.hyvor.com/b.png',
        ], $updater->getMatched());
    }

}
