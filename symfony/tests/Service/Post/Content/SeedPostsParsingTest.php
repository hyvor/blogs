<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content;

use App\Service\Post\Content\Nodes\Audio\Audio;
use App\Service\Post\Content\Nodes\Audio\AudioAttrs;
use App\Service\Post\Content\Nodes\Bookmark\Bookmark;
use App\Service\Post\Content\Nodes\Bookmark\BookmarkAttrs;
use App\Service\Post\Content\Nodes\Button\Button;
use App\Service\Post\Content\Nodes\Button\ButtonAttrs;
use App\Service\Post\Content\Nodes\Callout\Callout;
use App\Service\Post\Content\Nodes\Callout\CalloutAttrs;
use App\Service\Post\Content\Nodes\CodeBlock\CodeBlock;
use App\Service\Post\Content\Nodes\CodeBlock\CodeBlockAttrs;
use App\Service\Post\Content\Nodes\Embed\Embed;
use App\Service\Post\Content\Nodes\Embed\EmbedAttrs;
use App\Service\Post\Content\Nodes\Figure;
use App\Service\Post\Content\Nodes\Table\Table;
use App\Service\Post\Content\Nodes\Toc\Toc;
use App\Service\Post\Content\PostSchema;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The files in resources/posts are parsed as seed content whenever a blog is created
 * (see BlogCreator::fillPosts). If any of them contain HTML the schema can't parse,
 * blog creation breaks for every new blog.
 */
#[CoversClass(PostSchema::class)]
class SeedPostsParsingTest extends KernelTestCase
{
    private function postSchema(): PostSchema
    {
        return $this->getService(PostSchema::class);
    }

    private static function postsDir(): string
    {
        return __DIR__ . '/../../../../resources/posts';
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function seedPostFilesProvider(): iterable
    {
        $files = glob(self::postsDir() . '/*.html') ?: [];

        foreach ($files as $file) {
            yield basename($file) => [$file];
        }
    }

    #[DataProvider('seedPostFilesProvider')]
    public function test_seed_post_file_parses_without_error(string $file): void
    {
        $html = (string) file_get_contents($file);

        $doc = $this->postSchema()->documentFromHtml($html);
        $array = $doc->toArray();

        $this->assertSame('doc', $array['type']);
        $this->assertNotEmpty($doc->content->all(), basename($file) . ' parsed to an empty document');
    }

    public function test_at_least_one_seed_post_file_exists(): void
    {
        $files = glob(self::postsDir() . '/*.html') ?: [];

        $this->assertNotEmpty($files);
    }

    public function test_content_style_post_contains_two_callouts_with_different_emoji_and_colors(): void
    {
        $callouts = $this->contentStyleNodes(Callout::class);

        $this->assertCount(2, $callouts);

        $firstAttrs = $callouts[0]->attrs;
        $secondAttrs = $callouts[1]->attrs;
        $this->assertInstanceOf(CalloutAttrs::class, $firstAttrs);
        $this->assertInstanceOf(CalloutAttrs::class, $secondAttrs);

        $this->assertSame('💡', $firstAttrs->emoji);
        $this->assertSame('#f1f1ef', $firstAttrs->bg);
        $this->assertSame('#000000', $firstAttrs->fg);

        // second callout uses a different emoji/color combination
        $this->assertNotSame($firstAttrs->emoji, $secondAttrs->emoji);
        $this->assertNotSame($firstAttrs->bg, $secondAttrs->bg);
    }

    public function test_content_style_post_contains_a_button_with_href_and_text(): void
    {
        $buttons = $this->contentStyleNodes(Button::class);

        $this->assertCount(1, $buttons);

        $attrs = $buttons[0]->attrs;
        $this->assertInstanceOf(ButtonAttrs::class, $attrs);

        $this->assertSame('https://blogs.hyvor.com', $attrs->href);
        $this->assertSame('Get Started', $buttons[0]->allText());
    }

    public function test_content_style_post_contains_a_table_of_contents(): void
    {
        $this->assertCount(1, $this->contentStyleNodes(Toc::class));
    }

    public function test_content_style_post_contains_a_bookmark(): void
    {
        $bookmarks = $this->contentStyleNodes(Bookmark::class);

        $this->assertCount(1, $bookmarks);

        $attrs = $bookmarks[0]->attrs;
        $this->assertInstanceOf(BookmarkAttrs::class, $attrs);
        $this->assertSame('https://github.com', $attrs->url);
    }

    public function test_content_style_post_contains_an_embed(): void
    {
        $embeds = $this->contentStyleNodes(Embed::class);

        $this->assertCount(1, $embeds);

        $attrs = $embeds[0]->attrs;
        $this->assertInstanceOf(EmbedAttrs::class, $attrs);
        $this->assertSame('https://www.youtube.com/watch?v=libKVRa01L8', $attrs->url);
    }

    public function test_content_style_post_contains_an_audio_block(): void
    {
        $audios = $this->contentStyleNodes(Audio::class);

        $this->assertCount(1, $audios);

        $attrs = $audios[0]->attrs;
        $this->assertInstanceOf(AudioAttrs::class, $attrs);
        $this->assertNotSame('', $attrs->src);
    }

    public function test_content_style_post_contains_a_javascript_code_block(): void
    {
        $codeBlocks = $this->contentStyleNodes(CodeBlock::class);

        $this->assertCount(1, $codeBlocks);

        $attrs = $codeBlocks[0]->attrs;
        $this->assertInstanceOf(CodeBlockAttrs::class, $attrs);
        $this->assertSame('javascript', $attrs->language);
        $this->assertStringContainsString('TalkClient', $codeBlocks[0]->allText());
    }

    public function test_content_style_post_contains_two_tables(): void
    {
        $this->assertCount(2, $this->contentStyleNodes(Table::class));
    }

    public function test_content_style_post_contains_a_figure(): void
    {
        $this->assertNotEmpty($this->contentStyleNodes(Figure::class));
    }

    /**
     * @param class-string<NodeType> $type
     * @return Node[]
     */
    private function contentStyleNodes(string $type): array
    {
        $html = (string) file_get_contents(self::postsDir() . '/post-content-style.html');
        $doc = $this->postSchema()->documentFromHtml($html);

        return $doc->getNodes($type);
    }
}
