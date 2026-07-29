<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
class AssetUrlFilterTest extends KernelTestCase
{
    /**
     * @param array<string, mixed> $context
     */
    private function render(string $template, array $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_asset_url_filter(): void
    {
        $url = 'https://myblog.com';
        $result = $this->render(
            "{{ 'script.js' | asset_url }}",
            ['_blog' => ['base_url' => $url]]
        );
        $this->assertSame("$url/assets/script.js", $result);
    }
}
