<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes\Toc;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Post\Content\Nodes\Toc\Toc;
use App\Service\Post\Content\PostContentService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
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
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertSame(
            '<div class="toc" data-levels="1,2,3,4"><ul><li><a href="#my-big-heading">My big heading</a></li></ul></div><h1 id="my-big-heading"><a href="#my-big-heading">My big heading</a></h1>',
            $html
        );
    }

    public function test_complex_toc(): void
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
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2, 'id' => 'my-smaller-heading'],
                    'content' => [['type' => 'text', 'text' => 'My smaller heading']],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 3, 'id' => 'my-little-heading'],
                    'content' => [['type' => 'text', 'text' => 'My little heading']],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 3, 'id' => 'my-little-heading2'],
                    'content' => [['type' => 'text', 'text' => 'My little heading 2']],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 1, 'id' => 'my-big-heading-2'],
                    'content' => [['type' => 'text', 'text' => 'My big heading 2']],
                ],
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 5, 'id' => 'my-way-smaller-heading'],
                    'content' => [['type' => 'text', 'text' => 'My way smaller heading']],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertSame(
            '<div class="toc" data-levels="1,2,3,4"><ul><li><a href="#my-big-heading">My big heading</a><ul><li><a href="#my-smaller-heading">My smaller heading</a><ul><li><a href="#my-little-heading">My little heading</a></li><li><a href="#my-little-heading2">My little heading 2</a></li></ul></li></ul></li><li><a href="#my-big-heading-2">My big heading 2</a></li></ul></div><h1 id="my-big-heading"><a href="#my-big-heading">My big heading</a></h1><h2 id="my-smaller-heading"><a href="#my-smaller-heading">My smaller heading</a></h2><h3 id="my-little-heading"><a href="#my-little-heading">My little heading</a></h3><h3 id="my-little-heading2"><a href="#my-little-heading2">My little heading 2</a></h3><h1 id="my-big-heading-2"><a href="#my-big-heading-2">My big heading 2</a></h1><h5 id="my-way-smaller-heading"><a href="#my-way-smaller-heading">My way smaller heading</a></h5>',
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
        ], JSON_THROW_ON_ERROR), $json);
    }

    public function test_disregards_ul_li_inside_toc(): void
    {
        $html = '<div class="toc" data-levels="1,2,3,4"><ul><li>My big heading</li></ul></div>' .
            '<ul><li>Other list</li></ul>';
        $json = $this->service()->getJsonFromHtml($html, $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'toc',
                    'attrs' => ['levels' => [1, 2, 3, 4]],
                ],
                [
                    'type' => 'bullet_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        ['type' => 'text', 'text' => 'Other list'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }

    public function test_with_custom_twig(): void
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
        ], JSON_THROW_ON_ERROR);

        $blog = BlogFactory::createOne();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'node-toc.twig',
            'content' => '<div>Table of Contents{{ toc | raw }}</div>',
        ]);

        $html = $this->service()->getHtml($json, $blog);

        $this->assertSame(
            '<div>Table of Contents<div class="toc" data-levels="1,2,3,4"><ul><li><a href="#my-big-heading">My big heading</a></li></ul></div></div><h1 id="my-big-heading"><a href="#my-big-heading">My big heading</a></h1>',
            $html
        );
    }
}
