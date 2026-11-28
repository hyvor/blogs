<?php

namespace App\Service\Blog\UpdateBlogUrls;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Lock\Key;
use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * Dispatched when a media URL changes (the media was renamed),
 * to update all references to the old media URL in the blog
 */
#[AsMessage(MessageTransport::ASYNC)]
readonly class UpdateBlogUrlsMessage
{

    public function __construct(
        public int $blogId,
        /**
         * @var array<Key> keys to release after processing
         */
        public array $lockKeys,
        public int $mediaId,
        public string $mediaOldUrl,
        public string $mediaNewUrl,
    ) {}

}
