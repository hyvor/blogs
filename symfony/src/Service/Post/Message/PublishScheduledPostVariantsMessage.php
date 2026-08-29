<?php

namespace App\Service\Post\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class PublishScheduledPostVariantsMessage
{

}
