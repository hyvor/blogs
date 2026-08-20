<?php

namespace App\Entity\Enum;

enum PostSuggestionType: string
{
    case INSERT = 'insert';
    case DELETE = 'delete';
    case FORMAT = 'format';
    case COMMENT = 'comment';
}
