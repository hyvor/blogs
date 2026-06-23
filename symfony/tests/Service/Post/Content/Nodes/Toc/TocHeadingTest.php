<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes\Toc;

use App\Service\Post\Content\Nodes\Toc\TocHeading;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TocHeading::class)]
class TocHeadingTest extends TestCase
{
    public function test_from_html(): void
    {
        $html = '<h1 id="heading-1">Heading 1</h1><h2 id="heading-2">Heading 2</h2>';
        $headings = TocHeading::fromHtml($html, [1, 2]);

        $this->assertCount(2, $headings);
        $this->assertSame('Heading 1', $headings[0]->title);
        $this->assertSame('heading-1', $headings[0]->id);
        $this->assertSame(1, $headings[0]->level);
        $this->assertSame('Heading 2', $headings[1]->title);
        $this->assertSame('heading-2', $headings[1]->id);
        $this->assertSame(2, $headings[1]->level);
    }

    public function test_from_html_filters_levels(): void
    {
        $html = '<h1 id="h1">H1</h1><h2 id="h2">H2</h2><h3 id="h3">H3</h3>';
        $headings = TocHeading::fromHtml($html, [1, 2]);

        $this->assertCount(2, $headings);
    }

    public function test_get_levels(): void
    {
        $this->assertSame([1, 2, 3], TocHeading::getLevels([1, 2, 3]));
        $this->assertSame([1, 2], TocHeading::getLevels('1,2'));
        $this->assertSame([1, 2, 3, 4, 5, 6], TocHeading::getLevels(null));
        $this->assertSame([1, 2], TocHeading::getLevels([1, 2, 7])); // 7 filtered out
    }
}
