<?php declare(strict_types=1);

namespace App\Helpers;

use Hyvor\Phrosemirror\Converters\HtmlSerializer\Context;
use Hyvor\Phrosemirror\Document\Node;


class TocHelper {
    static public function tocToHtml(Context $context): string 
    {
        $result = "<div class=\"toc\">";
        $currentLevel = 1;
        $offset = 0;
        // Go through all headings and create a TOC
        $context->topNode->traverse(function (Node $node) use (&$html, &$result, &$currentLevel, &$offset) {
            if ($node->type->name === 'heading') {
                // If the first heading is found, add a <ul> tag
                if ($result == "<div class=\"toc\">") {
                    $result .= "<ul>";
                    $currentLevel = $node->attrs->get('level');
                    $offset++;
                }

                $newNode = "<li><a href=\"#" . $node->attrs->get('id') . "\">" . $node->allText() . "</a></li>";

                // Smaller heading
                if ($node->attrs->get('level') > $currentLevel) {
                    $result .= "<ul>" . $newNode;
                    $currentLevel = $node->attrs->get('level');
                    $offset++;
                }

                // Bigger heading
                else if ($node->attrs->get('level') < $currentLevel) {
                    while ($node->attrs->get('level') < $currentLevel) {
                        $result .= "</ul>";
                        $currentLevel--;
                        $offset--;
                    }
                    $result .= $newNode;
                }
                
                else {
                    $result .= $newNode;
                }
            }
        });
        while ($offset > 0) {
            $result .= "</ul>";
            $offset--;
        }
        $result .= "</div>";
        return $result;
    }
}