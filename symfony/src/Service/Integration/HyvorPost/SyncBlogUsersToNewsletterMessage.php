<?php

namespace App\Service\Integration\HyvorPost;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
class SyncBlogUsersToNewsletterMessage
{
    public function __construct(public int $blogId) {}
}
