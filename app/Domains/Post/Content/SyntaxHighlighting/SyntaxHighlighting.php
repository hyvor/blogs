<?php

namespace App\Domains\Post\Content\SyntaxHighlighting;

use DomainException;
use Highlight\Highlighter;

/**
 * For syntax highlighting, we use scrivo/highlight.php library
 * As of 2022, it does not seem to be maintained properly
 * However, later, if we want we can change the library
 * Therefore, I'm abstracting that library inside this class
 */

class SyntaxHighlighting
{
    public static function highlight(
        string $code,
        string $language,
        bool $lineNumbers = true,
        array $lineHighlights = []
    ) {
        $highlighter = new Highlighter();

        try {
            $highlighted = $highlighter->highlight($language, $code);
        } catch (DomainException $e) {
            $highlighted = htmlentities($code);
        }

        $lines = splitCodeIntoArray($highlighted->value);

        $html = '<code>';
        foreach ($lines as $number => $line) {
            $realNumber = $number + 1;

            $highlightedClass = in_array($realNumber, $lineHighlights) ? "highlighted-line" : "";
            $html .= "<div class=\"$highlightedClass\">$line</div>";
        }
        $html .= '</code>';

        return <<<HTML
            <pre class="hljs">$html</pre>
        HTML;
    }
}



function splitCodeIntoArray($html)
{
    if (trim($html) === "") {
        return [];
    }

    $queuedPrefix = '';
    $regexWorkspace = [];
    $rawLines = preg_split('/\R/u', $html);

    if ($rawLines === false) {
        return false;
    }

    foreach ($rawLines as &$rawLine) {
        // If the previous line has been marked as "open", then we'll have something
        // in our queue
        if ($queuedPrefix !== '') {
            $rawLine = $queuedPrefix . $rawLine;
            $queuedPrefix = '';
        }

        // Find how many opening `<span>` tags exist on this line
        preg_match_all('/<span[^>]*+>/u', $rawLine, $regexWorkspace);
        $openingTags = count($regexWorkspace[0]);

        // Find all of the closing `</span>` tags that exist on this line
        preg_match_all('/<\/span>/u', $rawLine, $regexWorkspace);
        $closingTags = count($regexWorkspace[0]);

        // If the number of opening tags matches the number of closing tags, then
        // we don't have any new tags that span multiple lines
        if ($openingTags === $closingTags) {
            continue;
        }

        // Find all of the complete `<span>` tags and remove them from a working
        // copy of the line. Then we'll be left with just opening tags.
        $workingLine = preg_replace('/<span[^>]*+>[^<]*+<\/span>/u', '', $rawLine);
        preg_match_all('/<span[^>]*+>/u', $workingLine, $regexWorkspace);
        $queuedPrefix = implode('', $regexWorkspace[0]);

        // Close all of the remaining open tags on this line
        $diff = str_repeat('</span>', $openingTags - $closingTags);
        $rawLine .= $diff;
    }

    return $rawLines;
}
