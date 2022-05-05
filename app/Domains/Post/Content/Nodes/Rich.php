<?php

namespace App\Domains\Post\Content\Nodes;

use App\Domains\UrlData\UrlDataRepository;
use Exception;
use Tiptap\Core\Node;

class Rich extends Node
{
    public static $name = 'rich';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'rich[data-url]',
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
            $embed = UrlDataRepository::fetch($url);

            if ($embed->type === 'rich') {
                $embedContent = $embed->html;
            }
        } catch (Exception) {
        }

        return [
            'content' => $embedContent ? '<rich>' . $embedContent . '</rich>' : '',
        ];
    }
}
