<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
#[CoversClass(TwigRendererService::class)]
class TemplateFilterTest extends KernelTestCase
{
    /**
     * @param array<string, mixed> $context
     */
    private function render(string $template, array $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_template_filter(): void
    {
        $context = [
            'var1' => '{{ var2 }}',
            'var2' => 'test',
        ];

        // without template
        $this->assertSame('{{ var2 }}', $this->render('{{ var1 }}', $context));

        // with template
        $this->assertSame('test', $this->render('{{ var1 | template }}', $context));
    }
}
