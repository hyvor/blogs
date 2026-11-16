<?php

namespace App\Tests\Service\Blog\UpdateBlogUrls\Updater;

use App\Service\Blog\UpdateBlogUrls\Updater\LegacyMediaUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LegacyMediaUpdater::class)]
class LegacyMediaUpdaterTest extends TestCase
{

    public function test_updates_legacy_media_urls(): void
    {
        $updater = new LegacyMediaUpdater('https://example.com/blog', 'https://blogs.hyvor.com/media/AQ');

        $this->assertSame(
            'https://blogs.hyvor.com/media/AQ/image.png',
            $updater->update('https://example.com/blog/media/image.png')
        );
        $this->assertSame(
            'https://blogs.hyvor.com/media/AQ/image.png/500w',
            $updater->update('https://example.com/blog/media/image.png/500w')
        );
        $this->assertSame(2, $updater->getUpdatedCount());
    }

    public function test_ignores_other_urls(): void
    {
        $updater = new LegacyMediaUpdater('https://example.com/blog', 'https://blogs.hyvor.com/media/AQ');

        $this->assertFalse($updater->update('https://example.com/blog/post'));
        $this->assertFalse($updater->update('https://example.com/blog/media/'));
        $this->assertFalse($updater->update('https://example.com/media/image.png'));
        $this->assertFalse($updater->update('https://other.com/blog/media/image.png'));
        // already migrated
        $this->assertFalse($updater->update('https://blogs.hyvor.com/media/AQ/image.png'));
        $this->assertSame(0, $updater->getUpdatedCount());
    }

}
