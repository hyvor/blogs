<?php

namespace App\Service\CustomDomain\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class RegenerateExpiredTlsCertificatesMessage
{

}
