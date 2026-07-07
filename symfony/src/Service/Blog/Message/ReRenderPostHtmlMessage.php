<?php

namespace App\Service\Blog\Message;

use App\Service\App\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class ReRenderPostHtmlMessage
{
    public function __construct(public int $blogId) {}
}
