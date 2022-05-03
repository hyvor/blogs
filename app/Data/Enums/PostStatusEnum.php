<?php

namespace App\Data\Enums;

enum PostStatusEnum : string
{

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case SCHEDULED = 'scheduled';

}