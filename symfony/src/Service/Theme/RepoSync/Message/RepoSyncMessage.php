<?php

namespace App\Service\Theme\RepoSync\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class RepoSyncMessage
{
    public function __construct(public bool $createPreviewBlogs = true) {}
}
