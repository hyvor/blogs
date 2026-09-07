<?php

namespace App\Service\Integration\HyvorPost;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
class SyncBlogUsersToNewsletterMessage
{
    public function __construct(
        public int $blogId,
        // by default, syncs all users, but if hyvorUserId is set, only syncs that user
        public ?int $hyvorUserId = null,
        // if true, deletes the user from hyvor post instead of syncing
        public bool $delete = false,
    ) {}
}
