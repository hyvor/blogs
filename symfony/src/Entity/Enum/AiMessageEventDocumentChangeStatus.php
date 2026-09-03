<?php

namespace App\Entity\Enum;

enum AiMessageEventDocumentChangeStatus: string
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
}
