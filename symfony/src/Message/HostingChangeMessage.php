<?php

namespace App\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class HostingChangeMessage
{
    public function __construct(public int $hostingChangeId) {}
}
