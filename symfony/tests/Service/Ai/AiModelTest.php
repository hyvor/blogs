<?php

namespace App\Tests\Service\Ai;

use App\Service\Ai\AiModel;
use App\Service\Ai\AiProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AiModel::class)]
class AiModelTest extends TestCase
{

    public function test_default(): void
    {
        $this->assertSame(AiModel::GPT_5_6_LUNA, AiModel::default());
    }

    public function test_get_provider(): void
    {
        $this->assertSame(AiProvider::OPENAI, AiModel::GPT_5_6_LUNA->getProvider());
        $this->assertSame(AiProvider::ANTHROPIC, AiModel::CLAUDE_SONNET_5->getProvider());
        $this->assertSame(AiProvider::MISTRAL, AiModel::MISTRAL_SMALL_LATEST->getProvider());
    }

    public function test_get_cost(): void
    {
        $this->assertSame(0.20, AiModel::GPT_5_6_LUNA->getInputCost());
        $this->assertSame(1.20, AiModel::GPT_5_6_LUNA->getOutputCost());
    }

    public function test_get_input_cost_usd(): void
    {
        $this->assertSame(2.0, AiModel::GPT_5_6_LUNA->getInputCostUsd(10_000_000));
    }

    public function test_get_output_cost_usd(): void
    {
        $this->assertSame(12.0, AiModel::GPT_5_6_LUNA->getOutputCostUsd(10_000_000));
    }

}
