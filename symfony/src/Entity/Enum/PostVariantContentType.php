<?php

namespace App\Entity\Enum;

enum PostVariantContentType: string
{
    case CONTENT = 'content';
    case CONTENT_UNSAVED = 'content_unsaved';
}
