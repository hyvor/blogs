<?php

namespace App\Service\Integration\HyvorTalk;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
class SyncBlogUsersToWebsiteMessage
{
    public function __construct(
        public int $blogId,
        // by default, syncs all users, but if hyvorUserId is set, only syncs that user
        public ?int $hyvorUserId = null,
        /**
         * @var 'admin'|'mod'|null
         * the Hyvor Talk moderator role to sync the user with
         * required when hyvorUserId is set and delete is false
         */
        public ?string $role = null,
        // if true, removes the user from hyvor talk instead of syncing
        public bool $delete = false,
    ) {}
}
