<?php

namespace App\Service\CodeHighlight;

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;
use Phiki\Token\HighlightedToken;

/**
 * @phpstan-type HighlightResult array{
 *     pre: array{style: string, class: string, onmouseenter: string, onmouseleave: string},
 *     code: string,
 * }
 */
class Highlighter
{
    private Phiki $phiki;

    public function __construct()
    {
        $this->phiki = new Phiki();
    }

    /**
     * @return HighlightResult
     */
    public function highlight(
        string $code,
        string $language,
        string $themeName,
        bool $lineNumbers,
        string $annotations
    ): array {
        try {
            $theme = Theme::tryFrom($themeName) ?? Theme::GithubDark;
            $grammar = Grammar::tryFrom($language) ?? Grammar::Html;

            /** @var array<int, array<int, HighlightedToken>> $lines */
            $lines = $this->phiki->codeToHighlightedTokens($code, $grammar, $theme);
            $parsedTheme = $this->phiki->environment->themes->resolve($theme);
        } catch (\Throwable) {
            return ['pre' => ['style' => '', 'class' => '', 'onmouseenter' => '', 'onmouseleave' => ''], 'code' => htmlspecialchars($code)];
        }

        return $this->buildHtml($language, $lineNumbers, $lines, $parsedTheme, new Annotations($annotations));
    }

    /**
     * @return string[]
     */
    public function getAllThemes(): array
    {
        return array_map(fn(Theme $t) => $t->value, Theme::cases());
    }

    /**
     * @return string[]
     */
    public function getAllLanguages(): array
    {
        return array_map(fn(Grammar $g) => $g->value, Grammar::cases());
    }

    /**
     * @param array<int, array<int, HighlightedToken>> $lines
     * @return HighlightResult
     */
    private function buildHtml(
        string $language,
        bool $lineNumbers,
        array $lines,
        ParsedTheme $parsedTheme,
        Annotations $annotations
    ): array {
        $base = $parsedTheme->base();
        $fg = $base->foreground ?? '#000000';
        $bg = $base->background ?? '#ffffff';
        /** @var array<string, string> $colors */
        $colors = $parsedTheme->colors;

        $html = '';
        $lineNumber = 1;

        $hasLineNumbers = $lineNumbers && $annotations->hasLineNumbers();
        $maxLineNumber = max($annotations->getMaxLineNumber(), count($lines));

        $hasDiff = $annotations->hasDiffAdd() || $annotations->hasDiffRemove();
        $hasFocus = $annotations->hasFocus();

        $blur = 'blur(2px)';

        foreach ($lines as $index => $line) {
            $lineBackground = null;
            $lineFilter = null;
            $lineTransition = null;
            $lineClass = ['line'];

            $realLineNumber = $index + 1;

            $shouldFocus = $annotations->shouldFocus($realLineNumber);
            $shouldDiffAdd = $annotations->shouldDiffAdd($realLineNumber);
            $shouldDiffRemove = $annotations->shouldDiffRemove($realLineNumber);

            if ($shouldFocus) {
                $lineClass[] = 'focus';
            } elseif ($shouldDiffAdd) {
                $lineBackground = $colors['diffEditor.insertedTextBackground'] ?? '#00ff0022';
                $lineClass[] = 'diff-add';
            } elseif ($shouldDiffRemove) {
                $lineBackground = $colors['diffEditor.removedTextBackground'] ?? '#ff000022';
                $lineClass[] = 'diff-remove';
            } elseif ($annotations->shouldHighlight($realLineNumber)) {
                $lineBackground = $colors['editor.lineHighlightBackground']
                    ?? $colors['editor.selectionHighlightBackground']
                    ?? $colors['editor.selectionBackground']
                    ?? $bg;
                $lineClass[] = 'highlight';
            }

            if ($hasFocus && !$shouldFocus) {
                $lineFilter = $blur;
                $lineTransition = 'filter 0.3s';
            }

            $lineClassStr = implode(' ', $lineClass);
            $lineStyle = $this->toStyleString([
                'background-color' => $lineBackground,
                'filter' => $lineFilter,
                'transition' => $lineTransition,
            ]);

            $html .= "<div class=\"$lineClassStr\" style=\"$lineStyle\">";

            if ($hasLineNumbers) {
                $renumbered = $annotations->getRenumberedLineNumber($realLineNumber, $lineNumber);
                $html .= $this->lineNumberSpan($renumbered, $maxLineNumber, $colors, $fg);
                if ($renumbered !== false) {
                    $lineNumber = $renumbered;
                }
            }

            if ($hasDiff) {
                $html .= $this->diffMarkSpan($shouldDiffAdd, $shouldDiffRemove, $colors);
            }

            $html .= '<span>';
            if (count($line) === 0) {
                $html .= '<wbr />';
            }
            foreach ($line as $token) {
                $tokenColor = $token->settings['default']->foreground ?? $fg;
                $tokenContent = $token->token->text;
                $html .= '<span style="color:' . $tokenColor . '">' . htmlspecialchars($tokenContent) . '</span>';
            }
            $html .= '</span></div>';

            $lineNumber++;
        }

        $preStyle = $this->toStyleString(['background-color' => $bg]);
        $preClasses = ["language-$language"];
        $preOnMouseEnter = '';
        $preOnMouseLeave = '';

        if ($annotations->hasHighlight()) {
            $preClasses[] = 'has-highlight';
        }
        if ($hasFocus) {
            $preClasses[] = 'has-focus';
            $preOnMouseEnter = "this.querySelectorAll('.line:not(.focus)').forEach(function(line){line.style.filter = ''});";
            $preOnMouseLeave = "this.querySelectorAll('.line:not(.focus)').forEach(function(line){line.style.filter = '$blur'});";
        }
        if ($annotations->hasDiffAdd()) {
            $preClasses[] = 'has-diff-add';
        }
        if ($annotations->hasDiffRemove()) {
            $preClasses[] = 'has-diff-remove';
        }
        if ($annotations->hasError()) {
            $preClasses[] = 'has-annotation-error';
        }
        if ($lineNumbers) {
            $preClasses[] = 'has-line-numbers';
        }

        return [
            'pre' => [
                'style' => $preStyle,
                'class' => implode(' ', $preClasses),
                'onmouseenter' => $preOnMouseEnter,
                'onmouseleave' => $preOnMouseLeave,
            ],
            'code' => $html,
        ];
    }

    /**
     * @param array<string, string> $colors
     */
    private function lineNumberSpan(int|false $number, int $max, array $colors, string $defaultFg): string
    {
        $numLength = $number === false ? 0 : strlen((string) $number);
        $maxLength = strlen((string) $max);
        $diffLength = $maxLength - $numLength;

        $color = $colors['editorLineNumber.foreground'] ?? $defaultFg;

        $styles = $this->toStyleString([
            '-webkit-user-select' => 'none',
            'user-select' => 'none',
            'color' => $color,
            'text-align' => 'right',
        ]);

        $numberDisplay = str_repeat(' ', $diffLength) . ($number === false ? '' : $number);

        return "<span class=\"line-number\" style=\"$styles\" aria-hidden=\"true\">$numberDisplay</span>";
    }

    /**
     * @param array<string, string> $colors
     */
    private function diffMarkSpan(bool $shouldDiffAdd, bool $shouldDiffRemove, array $colors): string
    {
        $content = $shouldDiffAdd ? '+' : ($shouldDiffRemove ? '-' : ' ');

        if ($shouldDiffRemove) {
            $color = $colors['terminal.ansiRed'] ?? $colors['terminal.ansiBrightRed'] ?? '#f07178';
        } else {
            $color = $colors['terminal.ansiGreen'] ?? $colors['terminal.ansiBrightGreen'] ?? '#cceccd';
        }

        $style = $this->toStyleString([
            '-webkit-user-select' => 'none',
            'user-select' => 'none',
            'color' => $color,
        ]);

        return "<span class=\"diff-mark\" style=\"$style\">$content</span>";
    }

    /**
     * @param array<string, mixed> $styles
     */
    private function toStyleString(array $styles): string
    {
        $parts = [];
        foreach ($styles as $key => $value) {
            if ($value !== null && $value !== '' && $value !== false) {
                $parts[] = $key . ':' . (is_scalar($value) ? (string) $value : '');
            }
        }
        return implode(';', $parts);
    }
}
