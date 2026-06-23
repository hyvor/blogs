<?php

namespace App\Service\CodeHighlight;

use stdClass;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Highlighter
{
    /**
     * @return array<string, mixed>
     */
    public function highlight(
        string $code,
        string $language,
        string $themeName,
        bool $lineNumbers,
        string $annotations
    ): array {
        $data = $this->callJs([
            'type' => 'tokens',
            'theme' => $themeName,
            'code' => $code,
            'language' => $language,
        ]);

        if (!$data instanceof stdClass) {
            return ['pre' => ['style' => '', 'class' => '', 'onmouseenter' => '', 'onmouseleave' => ''], 'code' => htmlspecialchars($code)];
        }

        /** @var array<int, array<int, stdClass>> $tokens */
        $tokens = is_array($data->tokens) ? $data->tokens : [];
        $theme = $data->theme instanceof stdClass ? $data->theme : new stdClass();

        return $this->buildHtml($language, $lineNumbers, $tokens, $theme, new Annotations($annotations));
    }

    public function getAllThemes(): mixed
    {
        return $this->callJs(['type' => 'themes']);
    }

    /**
     * @param array<int, array<int, stdClass>> $tokens
     * @return array<string, mixed>
     */
    private function buildHtml(
        string $language,
        bool $lineNumbers,
        array $tokens,
        stdClass $theme,
        Annotations $annotations
    ): array {
        $fg = isset($theme->fg) && is_string($theme->fg) ? $theme->fg : '#000000';
        $bg = isset($theme->bg) && is_string($theme->bg) ? $theme->bg : '#ffffff';
        $colors = isset($theme->colors) && $theme->colors instanceof stdClass ? $theme->colors : new stdClass();

        $html = '';
        $lineNumber = 1;

        $hasLineNumbers = $lineNumbers && $annotations->hasLineNumbers();
        $maxLineNumber = max($annotations->getMaxLineNumber(), count($tokens));

        $hasDiff = $annotations->hasDiffAdd() || $annotations->hasDiffRemove();
        $hasFocus = $annotations->hasFocus();

        $blur = 'blur(2px)';

        foreach ($tokens as $index => $line) {
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
                $v = $colors->{'diffEditor.insertedTextBackground'} ?? '#00ff0022';
                $lineBackground = is_string($v) ? $v : '#00ff0022';
                $lineClass[] = 'diff-add';
            } elseif ($shouldDiffRemove) {
                $v = $colors->{'diffEditor.removedTextBackground'} ?? '#ff000022';
                $lineBackground = is_string($v) ? $v : '#ff000022';
                $lineClass[] = 'diff-remove';
            } elseif ($annotations->shouldHighlight($realLineNumber)) {
                $v = $colors->{'editor.lineHighlightBackground'}
                    ?? $colors->{'editor.selectionHighlightBackground'}
                    ?? $colors->{'editor.selectionBackground'}
                    ?? $bg;
                $lineBackground = is_string($v) ? $v : $bg;
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
                $tokenColor = isset($token->color) && is_string($token->color) ? $token->color : $fg;
                $tokenContent = isset($token->content) && is_string($token->content) ? $token->content : '';
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

    private function lineNumberSpan(int|false $number, int $max, stdClass $colors, string $defaultFg): string
    {
        $numLength = $number === false ? 0 : strlen((string) $number);
        $maxLength = strlen((string) $max);
        $diffLength = $maxLength - $numLength;

        $v = $colors->{'editorLineNumber.foreground'} ?? $defaultFg;
        $color = is_string($v) ? $v : $defaultFg;

        $styles = $this->toStyleString([
            '-webkit-user-select' => 'none',
            'user-select' => 'none',
            'color' => $color,
            'text-align' => 'right',
        ]);

        $numberDisplay = str_repeat(' ', $diffLength) . ($number === false ? '' : $number);

        return "<span class=\"line-number\" style=\"$styles\" aria-hidden=\"true\">$numberDisplay</span>";
    }

    private function diffMarkSpan(bool $shouldDiffAdd, bool $shouldDiffRemove, stdClass $colors): string
    {
        $content = $shouldDiffAdd ? '+' : ($shouldDiffRemove ? '-' : ' ');

        if ($shouldDiffRemove) {
            $v = $colors->{'terminal.ansiRed'} ?? $colors->{'terminal.ansiBrightRed'} ?? '#f07178';
        } else {
            $v = $colors->{'terminal.ansiGreen'} ?? $colors->{'terminal.ansiBrightGreen'} ?? '#cceccd';
        }
        $color = is_string($v) ? $v : '#888888';

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

    /**
     * @param array<string, mixed> $arguments
     */
    private function callJs(array $arguments): mixed
    {
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
            cwd: (string) realpath(dirname(__DIR__, 3) . '/js'),
            timeout: null,
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return json_decode($process->getOutput());
    }
}
