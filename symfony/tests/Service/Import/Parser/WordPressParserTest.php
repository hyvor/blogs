<?php

namespace App\Tests\Service\Import\Parser;

use App\Entity\Blog;
use App\Service\Import\ImportLog;
use App\Service\Import\Parser\WordPressParser;
use App\Service\Post\Content\Nodes\Audio\Audio;
use App\Service\Post\Content\Nodes\Embed\Embed;
use App\Service\Post\Content\Nodes\Image\Image;
use App\Service\Post\Content\PostContentService;
use App\Service\Import\Importer\ParserException;
use App\Service\Route\PermalinkService;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WordPressParser::class)]
class WordPressParserTest extends KernelTestCase
{
    /** @throws ParserException */
    private function parser(Blog $blog, string $path): WordPressParser
    {
        return new WordPressParser(
            $blog,
            $path,
            new ImportLog(),
            $this->getService(PermalinkService::class),
        );
    }

    /** @throws ParserException */
    public function test_parses_wordpress_file(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(['subdomain' => 'wp-import']);
        $path = __DIR__ . '/WordPress/example';

        $parser = $this->parser($blog, $path);
        $parser->parse();

        $posts = $parser->posts;
        $this->assertCount(3, $posts);
        $this->assertSame(1, $parser->duplicateCount);

        $helloWorldPost = null;
        $startupPost = null;
        foreach ($posts as $post) {
            if ($post->variants[0]->slug === 'hello-world') {
                $helloWorldPost = $post;
            } elseif ($post->variants[0]->slug === 'how-to-start-a-startup-blog-ultimate-guide-for-startup-blogs') {
                $startupPost = $post;
            }
        }

        $this->assertNotNull($helloWorldPost);
        $this->assertNotNull($helloWorldPost->featuredImageUrl);
        $this->assertStringStartsWith('file://' . $path . '/uploads/', $helloWorldPost->featuredImageUrl);

        $this->assertNotNull($startupPost);
        $content = $startupPost->variants[0]->content;
        $contentDoc = $this->getService(PostContentService::class)->getDocumentFromJson($content, $blog);

        $images = $contentDoc->getNodes(Image::class);
        $this->assertCount(2, $images);
        $this->assertSame(
            'file://' . $path . '/uploads/2024/04/Commentsreactions-ezgif.com-video-to-gif-converter.gif',
            $images[0]->attrs->get('src'),
        );
        $this->assertSame('https://hyvor.com/blog/media/image.png', $images[1]->attrs->get('src'));

        $audio = $contentDoc->getNodes(Audio::class);
        $this->assertCount(1, $audio);
        $this->assertSame('file://' . $path . '/uploads/2024/04/dream-big.mp3', $audio[0]->attrs->get('src'));

        $embeds = $contentDoc->getNodes(Embed::class);
        $this->assertCount(2, $embeds);
        $this->assertSame('https://www.youtube.com/watch?v=Z_88MJ2dwts', $embeds[0]->attrs->get('url'));
        $this->assertSame('https://twitter.com/HyvorBlogs/status/1839476390309023989', $embeds[1]->attrs->get('url'));
    }

    /** @throws ParserException */
    public function test_parses_non_ascii_correctly(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(['subdomain' => 'wp-import-utf']);
        $path = __DIR__ . '/WordPress/exampleutf';

        $parser = $this->parser($blog, $path);
        $parser->parse();

        $posts = $parser->posts;
        $this->assertCount(1, $posts);

        $post = $posts[0];
        $this->assertSame('Bard ou ChatGPT: qual é o melhor?', $post->variants[0]->title);

        $content = $post->variants[0]->content;
        $html = $this->getService(PostContentService::class)->getHtml($content, $blog);
        $this->assertSame(
            '<p>De modo geral, os usuários ainda não têm como acessar o Google Bard. Isso porque a ferramenta de inteligência artificial ainda está em faze experimental.</p>',
            $html,
        );
    }
}
