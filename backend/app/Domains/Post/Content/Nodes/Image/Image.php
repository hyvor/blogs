<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Image;

use App\Domains\Media\Image\ImageResizeService;
use App\Domains\Media\MediaRepository;
use App\Domains\Route\PermalinkRepository;
use App\Helpers\MimeTypes;
use App\Models\Blog;
use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Image extends NodeType
{

    public function __construct (private Blog $blog) {}

    public string $name = 'image';
    public string $attrs = ImageAttrs::class;

    public function toHtml(Node $node, string $children): string
    {

        $blog = $this->blog;

        $src = strval($node->attr('src'));

        $alt = strval($node->attr('alt'));
        $widthAttr = strval($node->attr('width'));
        $heightAttr = strval($node->attr('height'));

        $srcset = null;

        $mediaName = PermalinkRepository::getMediaNameFromPermalink($blog, $src);

        if (
            $mediaName &&
            ($media = MediaRepository::getByBlogIdAndName($blog->id, $mediaName)) &&
            $media->extension
        ) {

            $mimeType = MimeTypes::getMimeFromExtension($media->extension);

            if (ImageResizeService::isMimeTypeSupported($mimeType) && MediaRepository::getContents($media)) {

                $width = ImageResizeService::getImageWidth(MediaRepository::getContents($media));

                $srcset = $src . ' ' . $width . 'w';

                if ($width > 500) $srcset .= ', ' . $src . '/500w 500w';
                if ($width > 750) $srcset .= ', ' . $src . '/750w 750w';
                if ($width > 1000) $srcset .= ', ' . $src . '/1000w 1000w';
                if ($width > 1500) $srcset .= ', ' . $src . '/1500w 1500w';

            }

        }

        return '<img' .
            " src=\"$src\"" .
            ($alt ? " alt=\"$alt\"" : '') .
            ($widthAttr ? " width=\"$widthAttr\"" : '') .
            ($heightAttr ? " height=\"$heightAttr\"" : '') .
            ($srcset ? " srcset=\"$srcset\"" : '') .
        '>';

    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'img',
                getAttrs: function (DOMElement $node) {
                    $src = $node->getAttribute('src');
                    if (!$src) return false;

                    $data = [
                        'src' => $src
                    ];

                    $alt = $node->getAttribute('alt');
                    $width = $node->getAttribute('width');
                    $height = $node->getAttribute('height');

                    if ($alt) $data['alt'] = $alt;
                    if ($width) $data['width'] = $width;
                    if ($height) $data['height'] = $height;

                    return ImageAttrs::fromArray($data);
                },
            )
        ];
    }

}