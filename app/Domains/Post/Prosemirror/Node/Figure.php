<?php

namespace App\Domains\Post\Prosemirror\Node;

use ProseMirrorToHtml\Nodes\Node;

class Figure extends Node
{
    protected $nodeType = 'figure';
    protected $tagName = 'figure';
}
