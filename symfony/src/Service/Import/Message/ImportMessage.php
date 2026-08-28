<?php

namespace App\Service\Import\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class ImportMessage
{
    public function __construct(
        public int $importId,
        public string $sitemapUrl,
        public bool $importImages,
        /** @var array<string, mixed> */
        public array $scraperOptions,
    ) {
    }
}
