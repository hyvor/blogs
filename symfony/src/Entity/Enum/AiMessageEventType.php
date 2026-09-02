<?php

namespace App\Entity\Enum;

enum AiMessageEventType: string
{
    case TEXT = 'text';
    case THINKING = 'thinking';
    case QUERY = 'query';
    case DOCUMENT_CHANGE = 'document_change';
}
