<?php

namespace App\Service\Ai;

enum AiProvider: string
{
    case MISTRAL = 'mistral';
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';

    public function label(): string
    {
        return match ($this) {
            self::MISTRAL => 'Mistral',
            self::OPENAI => 'OpenAI',
            self::ANTHROPIC => 'Anthropic',
        };
    }
}
