<?php

namespace App\Service\Ai;

enum AiProvider: string
{
    case MISTRAL = 'mistral';
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';

    /**
     * @return non-empty-string
     */
    public function model(): string
    {
        return match ($this) {
            self::MISTRAL => 'mistral-large-latest',
            self::OPENAI => 'gpt-4o',
            self::ANTHROPIC => 'claude-sonnet-5',
        };
    }
}
