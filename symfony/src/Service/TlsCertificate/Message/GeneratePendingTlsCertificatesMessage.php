<?php

namespace App\Service\TlsCertificate\Message;

use App\Service\App\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class GeneratePendingTlsCertificatesMessage
{
    public function __construct(
        private ?int $blogId = null
    ) {}

    public function getBlogId(): ?int
    {
        return $this->blogId;
    }
}