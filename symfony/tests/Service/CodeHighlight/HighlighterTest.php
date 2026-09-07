<?php

namespace App\Tests\Service\CodeHighlight;

use App\Service\CodeHighlight\Annotations;
use App\Service\CodeHighlight\Highlighter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Highlighter::class)]
#[CoversClass(Annotations::class)]
class HighlighterTest extends TestCase
{

    public function test_highlight_helpers(): void
    {
        $highlighter = new Highlighter();

        $themes = $highlighter->getAllThemes();
        $languages = $highlighter->getAllLanguages();

        $this->assertContains('github-dark', $themes);
        $this->assertContains('php', $languages);
    }

    public function test_highlight(): void
    {

        $highlighter = new Highlighter();

        $html = $highlighter->highlight(
            code: '<?php echo "Hello World!";' . "\n" . 'echo "Hello Town!";',
            language:  'php',
            themeName: 'github-dark',
            lineNumbers: true,
            annotations: 'h=2 f=1 +3 -4'
        );

        $this->assertSame('background-color:#24292e', $html['pre']['style']);
        $this->assertSame('language-php has-highlight has-focus has-line-numbers', $html['pre']['class']);
        $this->assertStringContainsString('.line:not(.focus)', $html['pre']['onmouseenter']);
        $this->assertStringContainsString('.line:not(.focus)', $html['pre']['onmouseleave']);
        $this->assertStringContainsString('Hello World!', $html['code']);

    }

    public function test_diff(): void
    {
        $highlighter = new Highlighter();

        $html = $highlighter->highlight(
            code: <<<CODE
            <?php
            echo "Hello World!";
            echo "Hello Town!";
            CODE,
            language: 'php',
            themeName: 'github-dark',
            lineNumbers: true,
            annotations: '-=3 +=2',
        );

        $this->assertStringContainsString('has-diff-add has-diff-remove', $html['pre']['class']);
        $this->assertStringContainsString('class="line diff-remove"', $html['code']);
        $this->assertStringContainsString('class="line diff-add"', $html['code']);

    }

    public function test_renumber(): void
    {
        $highlighter = new Highlighter();

        $html = $highlighter->highlight(
            code: <<<JS
            console.log("Hello World!");
            console.log("Hello Town!");
            JS,
            language: 'javascript',
            themeName: 'github-dark',
            lineNumbers: true,
            annotations: 'renumber=1:10,2:null',
        );

        $this->assertStringContainsString('10', $html['code']);
    }


}
