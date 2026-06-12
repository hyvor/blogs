<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
#[CoversClass(TwigRendererService::class)]
class IsCurrentUrlFunctionTest extends KernelTestCase
{
    private function render(string $template, mixed $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_matches(): void
    {
        $baseUrl = 'https://myblog.com';
        $result = $this->render(
            "{% if is_current_url('$baseUrl/page') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => "$baseUrl/page"]]
        );
        $this->assertSame('yes', $result);
    }

    public function test_with_wrong_path(): void
    {
        $baseUrl = 'https://myblog.com';
        $result = $this->render(
            "{% if is_current_url('$baseUrl/page') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => "$baseUrl/wrong-page"]]
        );
        $this->assertSame('', $result);
    }

    public function test_with_wrong_domain(): void
    {
        $baseUrl = 'https://myblog.com';
        $result = $this->render(
            "{% if is_current_url('$baseUrl/page') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => 'https://otherdomain.com/page']]
        );
        $this->assertSame('', $result);
    }

    public function test_with_relative_url(): void
    {
        $baseUrl = 'https://myblog.com';
        $result = $this->render(
            "{% if is_current_url('page') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => "$baseUrl/page"]]
        );
        $this->assertSame('yes', $result);
    }

    // #66
    public function test_with_external_url_on_homepage(): void
    {
        $baseUrl = 'https://subdomain.myblog.com';
        $result = $this->render(
            "{% if is_current_url('https://external.com/about') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => $baseUrl]]
        );
        $this->assertSame('', $result);
    }

    public function test_with_wrong_relative_url(): void
    {
        $baseUrl = 'https://myblog.com';
        $result = $this->render(
            "{% if is_current_url('page2') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => "$baseUrl/page"]]
        );
        $this->assertSame('', $result);
    }
}
