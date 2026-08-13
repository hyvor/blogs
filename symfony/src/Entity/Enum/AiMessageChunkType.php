<?php

namespace App\Entity\Enum;

enum AiMessageChunkType: string
{
    case TEXT = 'text';
    case THINKING = 'thinking';
    case EVENT = 'event';
}
