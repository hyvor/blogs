<?php

namespace App\Tests\Service\Blog\UpdateBlogUrls\Updater;

use App\Service\Blog\UpdateBlogUrls\Updater\LegacyMediaUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LegacyMediaUpdater::class)]
class LegacyMediaUpdaterTest extends TestCase
{

    private function updater(): LegacyMediaUpdater
    {
        return new LegacyMediaUpdater(
            ['https://supun.com', 'https://example.com/blog'],
            'https://supun.hyvorblogs.io/media'
        );
    }

    public function test_updates_legacy_media_urls(): void
    {
        $updater = $this->updater();

        $this->assertSame(
            'https://supun.hyvorblogs.io/media/image.png',
            $updater->update('https://supun.com/media/image.png')
        );
        $this->assertSame(
            'https://supun.hyvorblogs.io/media/image.png/500w',
            $updater->update('https://example.com/blog/media/image.png/500w')
        );
        $this->assertSame(2, $updater->getUpdatedCount());
    }

    public function test_ignores_other_urls(): void
    {
        $updater = $this->updater();

        $this->assertFalse($updater->update('https://supun.com/post'));
        $this->assertFalse($updater->update('https://supun.com/media/'));
        $this->assertFalse($updater->update('https://example.com/media/image.png'));
        $this->assertFalse($updater->update('https://other.com/blog/media/image.png'));
        $this->assertFalse($updater->update('/media/image.png'));
        // already migrated
        $this->assertFalse($updater->update('https://supun.hyvorblogs.io/media/image.png'));
        $this->assertSame(0, $updater->getUpdatedCount());
    }

    public function test_without_legacy_urls(): void
    {
        $updater = new LegacyMediaUpdater([], 'https://supun.hyvorblogs.io/media');

        $this->assertFalse($updater->update('https://supun.com/media/image.png'));
        $this->assertSame(0, $updater->getUpdatedCount());
    }

}
