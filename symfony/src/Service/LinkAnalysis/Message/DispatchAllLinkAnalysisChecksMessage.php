<?php

namespace App\Service\LinkAnalysis\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class DispatchAllLinkAnalysisChecksMessage
{
}
