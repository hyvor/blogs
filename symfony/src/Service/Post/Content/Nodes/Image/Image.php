<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Image;

use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Image extends NodeType
{
    public string $name = 'image';
    public string $attrs = ImageAttrs::class;

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'img',
                getAttrs: function (DOMElement $node) {
                    $src = $node->getAttribute('src');
                    if (!$src) {
                        return false;
                    }

                    $data = ['src' => $src];
                    $alt = $node->getAttribute('alt');
                    $width = $node->getAttribute('width');
                    $height = $node->getAttribute('height');

                    if ($alt) {
                        $data['alt'] = $alt;
                    }
                    if ($width) {
                        $data['width'] = $width;
                    }
                    if ($height) {
                        $data['height'] = $height;
                    }

                    return ImageAttrs::fromArray($data);
                },
            ),
        ];
    }
}
