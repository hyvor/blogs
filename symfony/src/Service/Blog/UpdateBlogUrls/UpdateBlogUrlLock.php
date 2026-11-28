<?php

namespace App\Service\Blog\UpdateBlogUrls;

use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class UpdateBlogUrlLock
{

    /**
     * 5 minutes
     * Guessing that updating a blog's URLs should not take more than 5 minutes
     * Within 5 minutes, I guess we can update millions
     */
    const int TTL = 300;

    const LOCK_KEY_MEDIA_URL_CHANGED = 'update_blog_urls_media_url_changed';

    public function __construct(
        private LockFactory $lock
    ) {}

    /**
     * Acquired while the URL of a media is being updated,
     * so that the same media is not renamed again until the update is done.
     * Released by UpdateBlogUrlsMessageHandler.
     *
     * @return array{LockInterface, Key}
     */
    public function mediaUrlLock(int $mediaId): array
    {
        $key = new Key(self::LOCK_KEY_MEDIA_URL_CHANGED . '_' . $mediaId);
        $lock = $this->lock->createLockFromKey(
            $key,
            self::TTL,
            autoRelease: false
        );

        return [$lock, $key];
    }

}
