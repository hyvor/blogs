<?php

namespace App\Domains\Post\Prosemirror\Node;

use ProseMirrorToHtml\Nodes\Node;

class Figcaption extends Node
{
    protected $nodeType = 'figcaption';
    protected $tagName = 'figcaption';
}