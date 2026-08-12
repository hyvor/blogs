<?php

namespace App\Entity\Enum;

enum AiMessageChunkType: string
{
    case TEXT = 'text';
    case EVENT = 'event';
}
