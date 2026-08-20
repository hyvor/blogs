<?php

namespace App\Entity\Enum;

enum PostSuggestionStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case RESOLVED = 'resolved';
}
