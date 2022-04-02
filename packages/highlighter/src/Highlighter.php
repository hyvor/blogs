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

    public string $html;

    public static function highlight(
        string $code,
        string $language,
        string $themeName,
        bool $lineNumbers,
        string $annotations
    )
    {

        $highlighter = new self(
            $code, 
            $language, 
            $themeName,
            $lineNumbers,
            $annotations
        );
        
        $highlighter->tokens();
        $highlighter->html();

        return $highlighter->html;

    }


    public static function getAllLanguages()
    {

        $languages = self::callJs([
            'type' => 'languages'
        ]);

        return $languages;

    }

    public static function getAllThemes()
    {

        $themes = self::callJs([
            'type' => 'themes'
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

        $this->tokens =  $data->tokens;
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
            cwd: realpath(dirname(__DIR__) . '/js'),
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

        foreach ($lines as $index => $line) {

            $lineNumber = $index + 1;

            $lineBackground = match ($lineNumber) {
                2 => 
                    $this->theme->colors->{'editor.lineHighlightBackground'} ??
                    $this->theme->colors->{'editor.selectionHighlightBackground'} ?? 
                    $this->theme->colors->{'editor.selectionBackground'} ??
                    $backgroundColor,
                10 => $this->theme->colors->{'diffEditor.removedTextBackground'} ?? '#ff000022',
                11 => $this->theme->colors->{'diffEditor.insertedTextBackground'} ?? '#00ff0022',
                default => null
            };

            if ($lineBackground) {
                $lineStyle = $this->getStylesArrayAsCssString([
                    'background-color' => $lineBackground,
                ]);
            } else {
                $lineStyle = '';
            }

            $code .= "<div class=\"line\" style=\"$lineStyle\">";

            if ($this->lineNumbers) {
                $code .=  $this->getLineNumberSpan($lineNumber);
            }

            $numTokens = count($line);
            foreach ($line as $tokenIndex => $token) {

                /* if ($numTokens === $tokenIndex + 1) {
                    // last token - check for line annotations via comments
                    $annotations = $this->getLineAnnotations();
                } */

                $tokenColor = $token->color ?? $foregroundColor;
                $tokenContent = htmlspecialchars($token->content);
                
                $styles = [
                    "color: $tokenColor"
                ];

                // add font styles

                $styles = implode(';', $styles);

                $code .= "<span style=\"$styles\">$tokenContent</span>";

            }

            $code .= "</div>";

        }

        $preStyle = $this->getStylesArrayAsCssString([
            'background-color' => $backgroundColor
        ]);

        $preClasses = [];
        if ($this->annotations->hasHighlight()) {
            $preClasses[] = 'has-highlight';
        }
        if ($this->annotations->hasFocus()) {
            $preClasses[] = 'has-focus';
        }
        if ($this->annotations->hasDiffAdd()) {
            $preClasses[] = 'has-diff-add';
        }
        if ($this->annotations->hasDiffRemove()) {
            $preClasses[] = 'has-diff-remove';
        }


        $preClasses = implode(' ', $preClasses);

        $this->html = <<<HTML
            <pre style="$preStyle" class="$preClasses"><code>$code</code></pre>
        HTML;

    }

    private function getLineNumberSpan($number)
    {

        $color = $this->theme->colors->{'editorLineNumber.foreground'} ?? $this->theme->fg;

        $styles = $this->getStylesArrayAsCssString([
            '-webkit-user-select' => 'none',
            'user-select' => 'none',
            'color' => $color,
            'text-align' => 'right'
        ]);

        $numberDisplay = $number > 9 ? $number : " " . $number;

        return "<span class=\"line-number\" style=\"$styles\">$numberDisplay</span>";

    }

    private function getStylesArrayAsCssString(array $styles)
    {
        $keyed = [];
        foreach ($styles as $key => $value) {
            $keyed[] = "$key:$value";
        }
        return implode(';', $keyed);
    }

}