<?php

namespace Tests\Unit\Domains\Import\Parser;

use App\Domains\App\JobMessageLog;
use App\Domains\Import\Parser\Typepad\TypepadParser;
use Database\Factories\BlogFactory;
use Tests\Case\DatabaseTestCase;

class TypepadParserTest extends DatabaseTestCase
{

    public function test_parse(): void
    {
        $path = __DIR__ . '/data/typepad1';
        $blog = BlogFactory::withLanguageAndRoutes();
        $messages = new JobMessageLog();

        $parser = new TypepadParser(
            $blog,
            $path,
            $messages,
        );
        $parser->parse();

        $posts = $parser->posts;
        $this->assertCount(2, $posts);

        $post1 = $posts[0];
        $this->assertEquals('Commenting Platforms', $post1->variants[0]->title);
        $this->assertEquals('commenting-platforms', $post1->variants[0]->slug);
        $this->assertEquals('2025-05-25 16:16:02', $post1->publishedAt->toDateTimeString());

        $post2 = $posts[1];
        $this->assertEquals('Blogging Platforms', $post2->variants[0]->title);
        $this->assertEquals('blogging-platforms', $post2->variants[0]->slug);
        $this->assertEquals('2023-05-25 16:16:02', $post2->publishedAt->toDateTimeString());

        $this->assertStringContainsString(
            // local URL
            'data\/typepad1\/media\/image1.txt',
            $post2->variants[0]->content
        );
        $this->assertStringContainsString('file:\/\/', $post2->variants[0]->content);
        // unchanged external URL
        $this->assertStringContainsString('https:\/\/external.com\/image.png', $post2->variants[0]->content);
    }

}
