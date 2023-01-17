<?php

namespace Hyvor\SyntaxHighlighter;

use stdClass;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Highlighter
{
    private array $tokens;

    private stdClass $theme;

    private Annotations $annotations;

    public array $data = [];

    public static function highlight(
        string $code,
        string $language,
        string $themeName,
        bool $lineNumbers,
        string $annotations
    ): array {
        $highlighter = new self(
            $code,
            $language,
            $themeName,
            $lineNumbers,
            $annotations
        );

        $highlighter->tokens();
        $highlighter->html();

        return $highlighter->data;
    }

    public static function getAllLanguages()
    {
        $languages = self::callJs([
            'type' => 'languages',
        ]);

        return $languages;
    }

    public static function getAllThemes()
    {
        $themes = self::callJs([
            'type' => 'themes',
        ]);

        return $themes;
    }

    public function __construct(
        private string $code,
        private string $language,
        private string $themeName,
        private bool $lineNumbers,
        string $annotations
    ) {
        $this->annotations = new Annotations($annotations);
    }

    /**
     * Gets TextMate grammar tokens (via Shiki) by calling ../js/index.js via node
     */
    public function tokens()
    {
        $data = self::callJs([
            'type' => 'tokens',
            'theme' => $this->themeName,
            'code' => $this->code,
            'language' => $this->language,
        ]);

        $this->tokens = $data->tokens;
        $this->theme = $data->theme;
    }

    public static function callJs(array $arguments)
    {

        // code from https://github.com/spatie/shiki-php/blob/main/src/Shiki.php
        $command = [
            (new ExecutableFinder())->find('node', 'node', [
                '/usr/local/bin',
                '/opt/homebrew/bin',
            ]),
            'index.js',
            json_encode($arguments),
        ];

        $process = new Process(
            command: $command,
            cwd: realpath(dirname(__DIR__).'/js'),
            timeout: null,
        );

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return json_decode($process->getOutput());
    }

    /**
     * Converts tokens to HTML
     */
    public function html()
    {
        $foregroundColor = $this->theme->fg;
        $backgroundColor = $this->theme->bg;

        $lines = $this->tokens;

        $code = '';

        $lineNumber = 1;

        $hasLineNumbers = $this->lineNumbers && $this->annotations->hasLineNumbers();
        $maxLineNumber = max($this->annotations->getMaxLineNumber(), count($lines));

        $hasDiff = $this->annotations->hasDiffAdd() || $this->annotations->hasDiffRemove();
        $hasFocus = $this->annotations->hasFocus();

        $blur = 'blur(2px)';

        foreach ($lines as $index => $line) {
            $lineBackground = null;
            $lineFilter = null;
            $lineTransition = null;
            $lineClass = ['line'];

            $realLineNumber = $index + 1;

            $shouldFocus = $this->annotations->shouldFocus($realLineNumber);
            $shouldDiffAdd = $this->annotations->shouldDiffAdd($realLineNumber);
            $shouldDiffRemove = $this->annotations->shouldDiffRemove($realLineNumber);

            if ($shouldFocus) {
                $lineClass[] = 'focus';
            } elseif ($shouldDiffAdd) {
                $lineBackground = $this->theme->colors->{'diffEditor.insertedTextBackground'} ?? '#00ff0022';
                $lineClass[] = 'diff-add';
            } elseif ($shouldDiffRemove) {
                $lineBackground = $this->theme->colors->{'diffEditor.removedTextBackground'} ?? '#ff000022';
                $lineClass[] = 'diff-remove';
            } elseif ($this->annotations->shouldHighlight($realLineNumber)) {
                $lineBackground =
                    $this->theme->colors->{'editor.lineHighlightBackground'} ??
                    $this->theme->colors->{'editor.selectionHighlightBackground'} ??
                    $this->theme->colors->{'editor.selectionBackground'} ??
                    $backgroundColor;
                $lineClass[] = 'highlight';
            }

            if ($hasFocus && ! $shouldFocus) {
                $lineFilter = $blur;
                $lineTransition = 'filter 0.3s';
            }

            $lineClass = implode(' ', $lineClass);

            $lineStyle = $this->getStylesArrayAsCssString([
                'background-color' => $lineBackground,
                'filter' => $lineFilter,
                'transition' => $lineTransition,
            ]);

            $code .= "<div class=\"$lineClass\" style=\"$lineStyle\">";

            if ($hasLineNumbers) {
                $renumberedLineNumber = $this->annotations->getRenumberedLineNumber($realLineNumber, $lineNumber);
                $code .= $this->getLineNumberSpan(
                    $renumberedLineNumber,
                    $maxLineNumber
                );

                if ($renumberedLineNumber !== false) {
                    $lineNumber = $renumberedLineNumber;
                }
            }

            if ($hasDiff) {
                $code .= $this->getDiffMarkSpan($shouldDiffAdd, $shouldDiffRemove);
            }

            foreach ($line as $token) {
                $tokenColor = $token->color ?? $foregroundColor;
                $tokenContent = htmlspecialchars($token->content);

                $styles = [
                    "color: $tokenColor",
                ];

                // add font styles

                $styles = implode(';', $styles);

                $code .= "<span style=\"$styles\">$tokenContent</span>";
            }

            $code .= '</div>';

            $lineNumber++;
        }

        $preStyle = $this->getStylesArrayAsCssString([
            'background-color' => $backgroundColor,
        ]);

        $preClasses = ["language-$this->language"];
        $preOnMouseEnter = '';
        $preOnMouseLeave = '';

        if ($this->annotations->hasHighlight()) {
            $preClasses[] = 'has-highlight';
        }
        if ($hasFocus) {
            $preClasses[] = 'has-focus';
            $preOnMouseEnter = "this.querySelectorAll('.line:not(.focus)').forEach(function(line){line.style.filter = ''});";
            $preOnMouseLeave = "this.querySelectorAll('.line:not(.focus)').forEach(function(line){line.style.filter = '$blur'});";
        }
        if ($this->annotations->hasDiffAdd()) {
            $preClasses[] = 'has-diff-add';
        }
        if ($this->annotations->hasDiffRemove()) {
            $preClasses[] = 'has-diff-remove';
        }
        if ($this->annotations->hasError()) {
            $preClasses[] = 'has-annotation-error';
        }
        if ($this->lineNumbers) {
            $preClasses[] = 'has-line-numbers';
        }

        $preClasses = implode(' ', $preClasses);

        $this->data = [
            'pre' => [
                'style' => $preStyle,
                'class' => $preClasses,
                'onmouseenter' => $preOnMouseEnter,
                'onmouseleave' => $preOnMouseLeave,
            ],
            'code' => $code,
        ];

        /*$this->html = <<<HTML
            <pre style="$preStyle" class="$preClasses" onmouseenter="$preOnMouseEnter" onmouseleave="$preOnMouseLeave"><code>$code</code></pre>
        HTML;*/
    }

    /**
     * Spacing should be done correctly
     * Based on the max value
     */
    private function getLineNumberSpan(int|false $number, int $max)
    {
        $numLength = $number === false ? 0 : strlen((string) $number);
        $maxLength = strlen((string) $max);
        $diffLength = $maxLength - $numLength;

        $color = $this->theme->colors->{'editorLineNumber.foreground'} ?? $this->theme->fg;

        $styles = $this->getStylesArrayAsCssString([
            '-webkit-user-select' => 'none',
            'user-select' => 'none',
            'color' => $color,
            'text-align' => 'right',
        ]);

        $numberDisplay = str_repeat(' ', $diffLength).($number === false ? '' : $number);

        return "<span class=\"line-number\" style=\"$styles\">$numberDisplay</span>";
    }

    private function getStylesArrayAsCssString(array $styles)
    {
        $keyed = [];
        foreach ($styles as $key => $value) {
            if (! $value) {
                continue;
            }
            $keyed[] = "$key:$value";
        }

        return implode(';', $keyed);
    }

    private function getDiffMarkSpan(bool $shouldDiffAdd, bool $shouldDiffRemove)
    {
        $content = ' ';
        if ($shouldDiffAdd) {
            $content = '+';
        } elseif ($shouldDiffRemove) {
            $content = '-';
        }

        $style = $this->getStylesArrayAsCssString([
            '-webkit-user-select' => 'none',
            'user-select' => 'none',
            'color' => $shouldDiffRemove ?

                // red
                $this->theme->colors->{'terminal.ansiRed'} ??
                $this->theme->colors->{'terminal.ansiBrightRed'} ??
                '#f07178'

                :

                // green
                $this->theme->colors->{'terminal.ansiGreen'} ??
                $this->theme->colors->{'terminal.ansiBrightGreen'} ??
                '#cceccd',
        ]);

        return "<span class=\"diff-mark\" style=\"$style\">$content</span>";
    }
}
