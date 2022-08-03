<?php

namespace App\Domains\Post\Content\Nodes;

use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Domains\UrlData\UrlDataRepository;
use DOMElement;
use Exception;
use Tiptap\Core\Node;

class Embed extends Node
{
    public static $name = 'embed';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'x-embed[data-url]',
                'getAttrs' => fn (DOMElement $node) => [
                    'url' => $node->getAttribute('data-url'),
                ],
            ],
        ];
    }

    public function renderHTML($node)
    {
        $embedContent = '';
        $url = $node->attrs->url;

        try {

            /**
             * Usually, the embed URL is already resolved and saved in the database
             * at the time the user embeds it in the editor.
             * So, we don't have to worry about the applciation making a HTTP call
             * It is a simple database call
             */
            $urlData = UrlDataRepository::fetch($url, UrlDataFetchTypeEnum::EMBED);
            $embedContent = $urlData->html;
        } catch (Exception) {
        }

        return [
            'content' => $embedContent ? '<x-embed>'.$embedContent.'</x-embed>' : '',
        ];
    }
}
