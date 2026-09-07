<?php

namespace App\Entity\Enum;

// what a client's resolveSuggestion/resolveComment call decided - see
// App\Service\Post\Suggestion\PostSuggestionService::resolve()
enum PostSuggestionDecision: string
{
    case ACCEPT = 'accept';
    case REJECT = 'reject';
    case RESOLVE = 'resolve';

    public function toStatus(): PostSuggestionStatus
    {
        return match ($this) {
            self::ACCEPT => PostSuggestionStatus::ACCEPTED,
            self::REJECT => PostSuggestionStatus::REJECTED,
            self::RESOLVE => PostSuggestionStatus::RESOLVED,
        };
    }
}
