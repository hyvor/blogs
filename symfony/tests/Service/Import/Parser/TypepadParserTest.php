<?php

namespace App\Tests\Service\Import\Parser;

use App\Service\Import\ImportLog;
use App\Service\Import\Importer\ParserException;
use App\Service\Import\Parser\Typepad\TypepadParser;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TypepadParser::class)]
class TypepadParserTest extends KernelTestCase
{
    /** @throws ParserException */
    public function test_parse(): void
    {
        $path = __DIR__ . '/Typepad/data/typepad1';
        $blog = BlogFactory::createOneWithLanguageAndRoutes(['subdomain' => 'typepad-import']);

        $parser = new TypepadParser(
            $blog,
            $path,
            new ImportLog(),
            $this->getService(PermalinkService::class),
        );
        $parser->parse();

        $posts = $parser->posts;
        $this->assertCount(2, $posts);

        $post1 = $posts[0];
        $this->assertSame('Commenting Platforms', $post1->variants[0]->title);
        $this->assertSame('commenting-platforms', $post1->variants[0]->slug);
        $this->assertSame('2025-05-25 16:16:02', $post1->publishedAt->format('Y-m-d H:i:s'));

        $post2 = $posts[1];
        $this->assertSame('Blogging Platforms', $post2->variants[0]->title);
        $this->assertSame('blogging-platforms', $post2->variants[0]->slug);
        $this->assertSame('2023-05-25 16:16:02', $post2->publishedAt->format('Y-m-d H:i:s'));

        $this->assertStringContainsString(
            // local URL
            'data\/typepad1\/media\/image1.txt',
            $post2->variants[0]->content,
        );
        $this->assertStringContainsString('file:\/\/', $post2->variants[0]->content);
        // unchanged external URL
        $this->assertStringContainsString('https:\/\/external.com\/image.png', $post2->variants[0]->content);
    }
}
