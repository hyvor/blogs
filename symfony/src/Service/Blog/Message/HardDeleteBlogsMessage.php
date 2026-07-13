<?php

namespace App\Service\Blog\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class HardDeleteBlogsMessage
{

}
