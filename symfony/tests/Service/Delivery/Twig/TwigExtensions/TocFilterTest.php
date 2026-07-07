<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Service\Delivery\Twig\Toc\TocHeading;
use App\Service\Delivery\Twig\Toc\TocHtml;
use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
#[CoversClass(TwigRendererService::class)]
#[CoversClass(TocHeading::class)]
#[CoversClass(TocHtml::class)]
class TocFilterTest extends KernelTestCase
{
    /**
     * @param array<string, mixed> $context
     */
    private function render(string $template, array $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_with_default_levels(): void
    {
        $html = '<h1 id="my-big-heading">My big heading</h1>';

        $result = $this->render('{{ content | toc }}', ['content' => $html]);

        $this->assertSame(
            '<div class="toc" data-levels="1,2,3,4,5,6"><ul><li><a href="#my-big-heading">My big heading</a></li></ul></div>',
            $result
        );
    }

    public function test_with_custom_levels_array(): void
    {
        $html = '<h1 id="my-big-heading">My big heading</h1><h2 id="my-small-heading">My small heading</h2>';

        $result = $this->render('{{ content | toc(levels) }}', ['content' => $html, 'levels' => [1, 2]]);

        $this->assertSame(
            '<div class="toc" data-levels="1,2"><ul><li><a href="#my-big-heading">My big heading</a>'
            . '<ul><li><a href="#my-small-heading">My small heading</a></li></ul></li></ul></div>',
            $result
        );
    }

    public function test_with_levels_as_string(): void
    {
        $html = '<h1 id="my-big-heading">My big heading</h1><h2 id="my-small-heading">My small heading</h2>';

        $result = $this->render('{{ content | toc("1,2") }}', ['content' => $html]);

        $this->assertSame(
            '<div class="toc" data-levels="1,2"><ul><li><a href="#my-big-heading">My big heading</a>'
            . '<ul><li><a href="#my-small-heading">My small heading</a></li></ul></li></ul></div>',
            $result
        );
    }

    public function test_with_complex_nesting(): void
    {
        $html = '<h1 id="my-big-heading">My big heading</h1>'
            . '<h2 id="my-smaller-heading">My smaller heading</h2>'
            . '<h3 id="my-little-heading">My little heading</h3>'
            . '<h3 id="my-little-heading2">My little heading 2</h3>'
            . '<h1 id="my-big-heading-2">My big heading 2</h1>'
            . '<h5 id="my-way-smaller-heading">My way smaller heading</h5>';

        $result = $this->render('{{ content | toc(levels) }}', ['content' => $html, 'levels' => [1, 2, 3, 4]]);

        $this->assertSame(
            '<div class="toc" data-levels="1,2,3,4">'
            . '<ul><li><a href="#my-big-heading">My big heading</a>'
            . '<ul><li><a href="#my-smaller-heading">My smaller heading</a>'
            . '<ul><li><a href="#my-little-heading">My little heading</a></li>'
            . '<li><a href="#my-little-heading2">My little heading 2</a></li></ul>'
            . '</li></ul></li>'
            . '<li><a href="#my-big-heading-2">My big heading 2</a></li></ul></div>',
            $result
        );
    }

    public function test_with_no_headings(): void
    {
        $result = $this->render('{{ content | toc }}', ['content' => '<p>No headings here</p>']);

        $this->assertSame('<div class="toc" data-levels="1,2,3,4,5,6"><ul></ul></div>', $result);
    }
}
