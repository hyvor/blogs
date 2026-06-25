<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes\Toc;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Toc\TocHeading;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TocHeading::class)]
class TocHeadingTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    public function test_from_node(): void
    {
        $json = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 1, 'id' => 'my-big-heading'],
                    'content' => [['type' => 'text', 'text' => 'My big heading']],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2, 'id' => 'my-small-heading'],
                    'content' => [['type' => 'text', 'text' => 'My small heading']],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 3, 'id' => 'my-tiny-heading'],
                    'content' => [['type' => 'text', 'text' => 'My tiny heading']],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 4, 'id' => 'my-tiny-tiny-heading'],
                    'content' => [['type' => 'text', 'text' => 'My tiny tiny heading']],
                ],
            ],
        ];

        $node = $this->service()->getDocumentFromJson($json, $this->blog());
        $headings = TocHeading::fromNode($node, [1, 2, 3]);

        $this->assertCount(3, $headings);

        $this->assertSame('My big heading', $headings[0]->title);
        $this->assertSame('my-big-heading', $headings[0]->id);
        $this->assertSame(1, $headings[0]->level);

        $this->assertSame('My small heading', $headings[1]->title);
        $this->assertSame('my-small-heading', $headings[1]->id);
        $this->assertSame(2, $headings[1]->level);

        $this->assertSame('My tiny heading', $headings[2]->title);
        $this->assertSame('my-tiny-heading', $headings[2]->id);
        $this->assertSame(3, $headings[2]->level);
    }

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

    public function test_from_html_when_invalid_html(): void
    {
        // well, this is still not considered invalid by DOMDocument
        $invalidHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Invalid HTML</title>
</head>
<body>
    <div>
        <p>This is a paragraph with no closing tag.
    </div>
    <p>Another paragraph with no opening tag.</p>
</body>
</html>
HTML;

        $headings = TocHeading::fromHtml($invalidHtml, [1, 2, 3]);

        $this->assertSame([], $headings);
    }

    // bug - unicode chars problem
    // https://davidwalsh.name/domdocument-utf8-problem didn't work
    // https://stackoverflow.com/a/8218649/9059939
    public function test_from_html_with_unicode_chars(): void
    {
        // … is a unicode char
        $html = '<h1 id="my-big-heading">Hello Worlding…</h1>';
        $headings = TocHeading::fromHtml($html, [1, 2, 3]);

        $this->assertSame('Hello Worlding…', $headings[0]->title);
    }

    public function test_get_levels(): void
    {
        $this->assertSame([1, 2, 3], TocHeading::getLevels([1, 2, 3]));
        $this->assertSame([1, 2, 3, 4], TocHeading::getLevels([1, 2, 3, 4]));
        $this->assertSame([1, 2, 3, 4, 5, 6], TocHeading::getLevels([-1, 0, 1, 2, 3, 4, 5, 6, 7]));
        $this->assertSame([1, 2, 3], TocHeading::getLevels(['1', '2 ', 3]));

        // string
        $this->assertSame([1, 2, 3], TocHeading::getLevels('1,2,3'));
        $this->assertSame([1, 2, 3, 4, 6], TocHeading::getLevels('1,2,3,4,6,7'));
        $this->assertSame([1, 2, 3, 5, 6], TocHeading::getLevels('1,2,3|4,5,6,7'));
        $this->assertSame([1], TocHeading::getLevels('1|2|3'));

        // null
        $this->assertSame([1, 2, 3, 4, 5, 6], TocHeading::getLevels(null));
    }
}
