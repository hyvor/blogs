<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes\Toc;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Toc\Toc;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Toc::class)]
class TocTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    public function test_json_to_html(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'toc',
                    'attrs' => ['levels' => [1, 2, 3, 4]],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 1, 'id' => 'my-big-heading'],
                    'content' => [['type' => 'text', 'text' => 'My big heading']],
                ],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertSame(
            '<div class="toc" data-levels="1,2,3,4"><ul><li><a href="#my-big-heading">My big heading</a></li></ul></div><h1 id="my-big-heading"><a href="#my-big-heading">My big heading</a></h1>',
            $html
        );
    }

    public function test_html_to_json(): void
    {
        $html = '<div class="toc" data-levels="1,2,3,4"></div>';
        $json = $this->service()->getJsonFromHtml($html, $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'toc',
                    'attrs' => ['levels' => [1, 2, 3, 4]],
                ],
            ],
        ]), $json);
    }

    public function test_complex_toc(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'toc',
                    'attrs' => ['levels' => [1, 2]],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 1, 'id' => 'h1'],
                    'content' => [['type' => 'text', 'text' => 'H1']],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2, 'id' => 'h2'],
                    'content' => [['type' => 'text', 'text' => 'H2']],
                ],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertStringContainsString('<ul><li><a href="#h1">H1</a><ul><li><a href="#h2">H2</a></li></ul></li></ul>', $html);
    }
}
