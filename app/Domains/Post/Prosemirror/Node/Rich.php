<?php

namespace App\Domains\Post\Prosemirror\Node;

use App\Domains\Embed\EmbedRepository;
use Exception;
use ProseMirrorToHtml\Nodes\Node;

class Rich extends Node
{
    protected $nodeType = 'rich';
    protected $tagName = 'div';

    public function tag() {

        return [
            [
                'tag' => $this->tagName,
                'attrs' => [
                    'class' => "rich"
                ],
            ]
        ];

    }

    public function text() {

        $url = $this->node->attrs->url;

        try {

            /**
             * Usually, the embed URL is already resolved and saved in the database
             * at the time the user embeds it in the editor.
             * So, we don't have to worry about the applciation making a HTTP call
             * It is a simple database call
             */
            $embed = EmbedRepository::fetch($url);

            if ($embed->type === 'rich') {
                return $embed->html;
            }

        } catch (Exception) {
            return "";
        }


    }
}