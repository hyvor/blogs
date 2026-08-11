<?php

namespace App\Tests\Service\Post\Content;

use App\Service\Post\Content\Markdown\MarkdownParser;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;

class MarkdownParserTest extends KernelTestCase
{

    public function test_parsing(): void
    {
        $markdown = '# Hello World';

        $parser = new MarkdownParser();
        $parser->parse($markdown);
    }

}
