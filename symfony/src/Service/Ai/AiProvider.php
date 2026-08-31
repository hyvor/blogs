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
            self::OPENAI => 'gpt-5.6-luna',
            self::ANTHROPIC => 'claude-sonnet-5',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::MISTRAL => 'Mistral',
            self::OPENAI => 'OpenAI',
            self::ANTHROPIC => 'Anthropic',
        };
    }
}
