<?php

namespace App\Service\Export\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class ExportMessage
{
    public function __construct(public int $exportId) {}
}
