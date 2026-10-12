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
        yield 'unknown version' => [
            'https://res.cloudinary.com/dqabfne6s/image/upload/v1/blogs.hyvor.com/filler-images/post-featured-images/1.webp',
            false,
        ];
        yield 'filler image out of range' => [
            'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images/post-featured-images/21.webp',
            false,
        ];
        yield 'author image' => [
            'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images/author-images/5.webp',
            'https://media.hyvor.com/blogs.hyvor.com/filler-images/author-images/5.webp',
        ];
        yield 'other host' => ['https://example.com/a.png', false];
        yield 'already migrated' => ['https://media.hyvor.com/blogs.hyvor.com/a.png', false];
    }

    #[DataProvider('urls')]
    public function test_update(string $url, false|string $expected): void
    {
        $this->assertSame($expected, new CloudinaryUpdater()->update($url));
    }

    public function test_tracks_matched_and_unknown(): void
    {
        $known = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images/post-featured-images/2.webp';
        $unknown = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1/other.png';

        $updater = new CloudinaryUpdater();
        $updater->update($known);
        $updater->update('https://example.com/a.png');
        $updater->update($unknown);
        $updater->update($unknown);

        $this->assertSame(1, $updater->getUpdatedCount());
        $this->assertSame([
            $known => 'https://media.hyvor.com/blogs.hyvor.com/filler-images/post-featured-images/2.webp',
        ], $updater->getMatched());
        $this->assertSame([$unknown], $updater->getUnknown());
    }

}
