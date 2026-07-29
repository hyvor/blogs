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

    const LOCK_KEY_HOSTING_CHANGED = 'update_blog_urls_hosting_changed';
    const LOCK_KEY_MEDIA_URL_CHANGED = 'update_blog_urls_media_url_changed';

    public function __construct(
        private LockFactory $lock
    ) {}

    public function canUpdate(UpdateBlogUrlEvent $event, ?int $mediaId = null): true|BlogUrlLockException
    {
        // hosting updates can run if there is no another media update in progress
        if ($event === UpdateBlogUrlEvent::HOSTING_CHANGED) {
            if ($this->isLockAcquiredBySomeone($this->mediaUrlGlobalLock()[0])) {
                return new BlogUrlLockException('Cannot update hosting URL because a media URL update is in progress. Please try again later.');
            }
        }

        // media updates can run if there is no another media update for the same mediaId in progress
        if ($event === UpdateBlogUrlEvent::MEDIA_URL_CHANGED) {
            assert($mediaId !== null);
            if ($this->isLockAcquiredBySomeone($this->mediaUrlLock($mediaId)[0])) {
                return new BlogUrlLockException('Cannot update media URL because another process is already updating the media URL. Please try again later.');
            }
        }

        // no one can if there is a hosting update in progress
        if ($this->isLockAcquiredBySomeone($this->hostingChangedLock()[0])) {
            return new BlogUrlLockException(
                'Cannot update media URL because a hosting URL update is in progress. Please try again later.'
            );
        }

        return true;
    }

    // https://github.com/symfony/symfony/issues/36024
    private function isLockAcquiredBySomeone(LockInterface $lock): bool
    {
        if (!$lock->acquire()) {
            return true;
        }
        $lock->release();
        return false;
    }

    /**
     * This lock is acquired on any MEDIA_URL_CHANGED event
     * to prevent HOSTING_CHANGED event from running while a MEDIA_URL_CHANGED is running
     * @return array{LockInterface, Key}
     */
    public function mediaUrlGlobalLock(): array
    {
        $key = new Key(self::LOCK_KEY_HOSTING_CHANGED);
        $lock = $this->lock->createLockFromKey(
            $key,
            self::TTL,
            autoRelease: false
        );

        return [$lock, $key];
    }

    /**
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

    /**
     * This lock is acquired on HOSTING_CHANGED event.
     * There can only be one HOSTING_CHANGED event running at a time
     * @return array{LockInterface, Key}
     */
    public function hostingChangedLock(): array
    {
        $key = new Key(self::LOCK_KEY_HOSTING_CHANGED);
        $lock = $this->lock->createLockFromKey(
            $key,
            self::TTL,
            autoRelease: false
        );

        return [$lock, $key];
    }

}
