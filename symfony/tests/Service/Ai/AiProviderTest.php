<?php

namespace App\Tests\Service\Ai;

use App\Service\Ai\AiProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AiProvider::class)]
class AiProviderTest extends TestCase
{

    public function test_label(): void
    {
        $this->assertSame('OpenAI', AiProvider::OPENAI->label());
        $this->assertSame('Anthropic', AiProvider::ANTHROPIC->label());
        $this->assertSame('Mistral', AiProvider::MISTRAL->label());
    }

}
