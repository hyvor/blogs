<?php

namespace App\Service\Ai;

enum AiModel: string
{

    // OpenAI
    case GPT_5_6_LUNA = 'gpt-5.6-luna';
    case GPT_5_6_TERRA = 'gpt-5.6-terra';
    case GPT_5_6_SOL = 'gpt-5.6-sol';

    // Anthropic
    case CLAUDE_SONNET_5 = 'claude-sonnet-5';
    case CLAUDE_OPUS_5 = 'claude-opus-5';

    // doesn't support adaptive thinking, not included for now
    // case CLAUDE_HAIKU_4_5 = 'claude-haiku-4-5';

    // Mistral
    case MISTRAL_SMALL_LATEST = 'mistral-small-latest';
    case MISTRAL_MEDIUM_LATEST = 'mistral-medium-latest';
    case MISTRAL_LARGE_LATEST = 'mistral-large-latest';
    case ZAI_GLM_5_2 = 'zai-glm-5-2';

    public static function default(): self
    {
        return self::GPT_5_6_LUNA;
    }


    /**
     * Returns input and output cost per million tokens in USD for each model.
     * Last checked: 2026-08-31
     *
     * @return array{0: float, 1: float} [inputCost, outputCost]
     */
    public function getCost(): array
    {
        return match ($this) {
            // OpenAI: https://developers.openai.com/api/docs/pricing
            self::GPT_5_6_LUNA => [0.20, 1.20],
            self::GPT_5_6_TERRA => [2.00, 12.00],
            self::GPT_5_6_SOL => [4.00, 20.00],

            // Anthropic: https://platform.claude.com/docs/en/about-claude/pricing
            self::CLAUDE_SONNET_5 => [2.00, 10.00],
            self::CLAUDE_OPUS_5 => [5.00, 25.00],
            // self::CLAUDE_HAIKU_4_5 => [1.00, 5.00],

            // Mistral: https://mistral.ai/pricing/api/
            self::MISTRAL_SMALL_LATEST => [0.15, 0.60],
            self::MISTRAL_MEDIUM_LATEST => [1.5, 7.5],
            self::MISTRAL_LARGE_LATEST => [0.50, 1.50],
            self::ZAI_GLM_5_2 => [1.40, 4.40],
        };
    }

    public function getInputCost(): float
    {
        return $this->getCost()[0];
    }
    public function getOutputCost(): float
    {
        return $this->getCost()[1];
    }

    public function getProvider(): AiProvider
    {
        return match ($this) {
            self::GPT_5_6_LUNA, self::GPT_5_6_TERRA, self::GPT_5_6_SOL => AiProvider::OPENAI,
            self::CLAUDE_SONNET_5, self::CLAUDE_OPUS_5 => AiProvider::ANTHROPIC,
            // glm-5.2 is hosted by Mistral
            self::MISTRAL_SMALL_LATEST, self::MISTRAL_MEDIUM_LATEST, self::MISTRAL_LARGE_LATEST, self::ZAI_GLM_5_2 => AiProvider::MISTRAL,
        };
    }

    /**
     * USD cost for the given number of input tokens.
     */
    public function getInputCostUsd(int $tokens): float
    {
        return round($tokens / 1_000_000 * $this->getInputCost(), 6);
    }

    /**
     * USD cost for the given number of output tokens.
     */
    public function getOutputCostUsd(int $tokens): float
    {
        return round($tokens / 1_000_000 * $this->getOutputCost(), 6);
    }

    /**
     * A percent value of model's average cost relative to others. Used in /usage frontend
     */
    public function getRelativeCostPercent(): float
    {
        $costs = array_map(
            fn(self $model) => ($model->getInputCost() + $model->getOutputCost()) / 2,
            self::cases()
        );

        $min = min($costs);
        $max = max($costs);
        $avg = ($this->getInputCost() + $this->getOutputCost()) / 2;

        if ($max <= $min) {
            return 0.0;
        }

        return max(1, round((($avg - $min) / ($max - $min)) * 100, 1));
    }
}
