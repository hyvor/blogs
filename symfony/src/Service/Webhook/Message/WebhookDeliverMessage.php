<?php

namespace App\Service\Webhook\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class WebhookDeliverMessage
{
    public function __construct(public int $deliveryId) {}
}
