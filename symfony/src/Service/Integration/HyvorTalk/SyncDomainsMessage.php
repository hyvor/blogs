<?php

namespace App\Service\Integration\HyvorTalk;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
class SyncDomainsMessage
{
    public function __construct(public int $blogId) {}
}
