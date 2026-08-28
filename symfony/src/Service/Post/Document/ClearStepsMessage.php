<?php

namespace App\Service\Post\Document;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class ClearStepsMessage {}
