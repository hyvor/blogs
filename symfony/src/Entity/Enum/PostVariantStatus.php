<?php

namespace App\Entity\Enum;

enum PostVariantStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
}
