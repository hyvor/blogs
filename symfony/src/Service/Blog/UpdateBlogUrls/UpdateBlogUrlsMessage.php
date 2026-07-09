<?php

namespace App\Service\Blog\UpdateBlogUrls;

use App\Service\App\MessageTransport;
use App\Service\Blog\Event\BlogHostingChangedEvent;
use App\Service\Media\Event\MediaNameUpdatedEvent;
use Symfony\Component\Lock\Key;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class UpdateBlogUrlsMessage
{

    public function __construct(
        public int $blogId,
        public UpdateBlogUrlEvent $event,
        /**
         * @var array<Key> keys to release after processing
         */
        public array $lockKeys,

        public ?string $blogOldUrl = null,
        public ?string $blogNewUrl = null,

        public ?int $mediaId = null,
        public ?string $mediaOldUrl = null,
        public ?string $mediaNewUrl = null,
    ) {}

}
