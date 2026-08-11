<?php

namespace App\Service\Post\Content\Markdown;

use Hyvor\Phrosemirror\Document\Node;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Parser\MarkdownParser as CommonMarkParser;

class MarkdownParser
{

    public function parse(string $markdown): Node
    {

        $environment = new Environment([
            'html_input' => 'strip',
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());

        $parser = new CommonMarkParser($environment);
        $document = $parser->parse($markdown);
        dd($document);

    }

}
