<?php

namespace App\Domains\Post\Content\Nodes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use Hyvor\SyntaxHighlighter\Highlighter;
use Tiptap\Core\Node;

class CodeBlock extends Node
{
    public static $name = 'code_block';

    public static $marks = '';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'pre',
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'language' => [
                'parseHTML' => function ($DOMNode) {
                    return preg_replace(
                        "/^language-/",
                        "",
                        $DOMNode->getAttribute('class')
                    ) ?: null;
                },
                'rendered' => false,
            ],
            'name' => [
                'parseHTML' => fn ($node) => $node->getAttribute('data-name'),
            ],
            'annotations' => [
                'parseHTML' => fn ($node) => $node->getAttribute('data-annotations'),
            ],
        ];
    }

    public function renderHTML($node)
    {

        /**
         * @var Blog $blog
         */
        $blog = $this->options['blog'];

        $syntaxOn = $blog->getMeta('syntax_on');
        $lineNumbers = $blog->getMeta('syntax_line_numbers');
        $themeName = $blog->getMeta('syntax_theme') ?? 'nord';

        $code = $node->content[0]->text ?? '';

        $language = $node->attrs->language ?? 'plain';
        $annotations = $node->attrs->annotations ?? '';
        $fileName = $node->attrs->name ?? "";

        $pre = [
            'style' => '',
            'class' => '',
            'onmouseenter' => '',
            'onmouseleave' => '',
        ];

        if ($syntaxOn) {
            [
                'pre' => $pre,
                'code' => $code
            ] = Highlighter::highlight(
                code: $code,
                language: $language,
                themeName: $themeName,
                lineNumbers: $lineNumbers,
                annotations: $annotations
            );
        }

        $template = ThemeFilesRepository::getFile(
            $blog,
            'block-code.twig',
            ThemeFileFolderEnum::TEMPLATES
        )?->content;

        if (! $template) {
            $template = PostContentRepository::getDefaultBlockTemplate('code');
        }

        $content = TwigRenderer::renderString($template, [
            'data' => [
                'pre' => $pre,
                'code' => $code,
                'language' => $language,
                'name' => $fileName,
                'theme' => $themeName,
                'line_numbers' => $lineNumbers,
            ],
        ]);

        return [
            'content' => $content,
        ];
    }
}
