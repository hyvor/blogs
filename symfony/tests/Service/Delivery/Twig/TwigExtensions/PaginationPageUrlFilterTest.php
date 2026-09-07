<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
#[CoversClass(TwigRendererService::class)]
class PaginationPageUrlFilterTest extends KernelTestCase
{
    /**
     * @param array<string, mixed> $context
     */
    private function render(string $template, array $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_pagination_page_url(): void
    {
        $baseUrl = 'https://myblog.com';

        $this->assertSame($baseUrl, $this->render(
            '{{ 1 | pagination_page_url }}',
            ['_meta' => ['url' => $baseUrl]]
        ));

        $this->assertSame($baseUrl, $this->render(
            '{{ 1 | pagination_page_url }}',
            ['_meta' => ['url' => $baseUrl . '/page/2']]
        ));

        $this->assertSame("$baseUrl/page/2", $this->render(
            '{{ 2 | pagination_page_url }}',
            ['_meta' => ['url' => $baseUrl]]
        ));

        $this->assertSame("$baseUrl/page/2", $this->render(
            '{{ 2 | pagination_page_url }}',
            ['_meta' => ['url' => "$baseUrl/page/3"]]
        ));
    }
}
