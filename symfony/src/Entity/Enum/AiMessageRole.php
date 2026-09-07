<?php

namespace App\Entity\Enum;

enum AiMessageRole: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';
}
