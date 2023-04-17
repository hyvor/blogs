<?php

namespace App\Domains\Post\Content\Nodes;

use App\Data\Enums\ResultEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Data\Objects\ConsoleAPI\UrlDataObject;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\UrlData\UrlDataRepository;
use App\Models\Blog;
use DOMElement;
use Exception;
use Tiptap\Core\Node;

class Bookmark extends Node
{
    public static $name = 'bookmark';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'a[class="bookmark"]',
                'getAttrs' => fn (DOMElement $node) => [
                    'url' => $node->getAttribute('data-url'),
                ],
            ],
        ];
    }

    public function renderHTML($node)
    {
        /**
         * @var Blog $blog
         */
        $blog = $this->options['blog'];

        $url = $node->attrs?->url;
        $empty = ['content' => ''];

        if (! $url) {
            return $empty;
        }

        try {

            $urlData = UrlDataRepository::fetch($url, UrlDataFetchTypeEnum::LINK);
            if ($urlData->result === ResultEnum::ERR) {
                return $empty;
            }

        } catch (Exception) {
            return $empty;
        }

        $template = ThemeFilesRepository::getFile(
            $blog,
            'block-bookmark.twig',
            ThemeFileFolderEnum::TEMPLATES
        )?->content;

        if (! $template) {
            $template = PostContentRepository::getDefaultBlockTemplate('bookmark');
        }

        $content = TwigRenderer::renderString($template, [
            'data' => new UrlDataObject($urlData),
        ]);

        return [
            'content' => $content,
        ];
    }
}
