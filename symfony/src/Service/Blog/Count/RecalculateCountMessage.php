<?php

namespace App\Service\Blog\Count;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class RecalculateCountMessage
{
    /**
     * @param CountType[] $types
     */
    public function __construct(
        public int $blogId,
        public array $types,
    ) {}
}
