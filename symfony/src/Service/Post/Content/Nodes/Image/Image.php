<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Image;

use App\Entity\Blog;
use App\Service\Delivery\MimeTypes;
use App\Service\Media\ImageResizeService;
use App\Service\Media\MediaService;
use App\Service\Route\PermalinkService;
use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Image extends NodeType
{
    public string $name = 'image';
    public string $attrs = ImageAttrs::class;

    public function __construct(
        private ?Blog $blog,
        private PermalinkService $permalinkService,
        private MediaService $mediaService,
        private ImageResizeService $imageResizeService,
    ) {}

    public function toHtml(Node $node, string $children): string
    {
        /** @var string $src */
        $src = $node->attr('src') ?? '';
        /** @var string $alt */
        $alt = $node->attr('alt') ?? '';
        /** @var string $widthAttr */
        $widthAttr = $node->attr('width') ?? '';
        /** @var string $heightAttr */
        $heightAttr = $node->attr('height') ?? '';

        $srcset = null;
        $mediaName = $this->getMediaNameFromPermalink($src);

        if (
            $this->blog &&
            $mediaName &&
            ($media = $this->mediaService->getMediaByBlogAndName($this->blog, $mediaName)) &&
            $media->getExtension()
        ) {
            $mimeType = MimeTypes::getMimeFromExtension($media->getExtension());

            // TODO: image width should be pre-stored
            if ($this->imageResizeService->isMimeTypeSupported($mimeType)) {
                $contents = $this->mediaService->getContents($media);
                if ($contents !== null) {
                    $width = $this->imageResizeService->getImageWidth($contents);

                    $srcset = $src . ' ' . $width . 'w';

                    if ($width > 500) {
                        $srcset .= ', ' . $src . '/500w 500w';
                    }
                    if ($width > 750) {
                        $srcset .= ', ' . $src . '/750w 750w';
                    }
                    if ($width > 1000) {
                        $srcset .= ', ' . $src . '/1000w 1000w';
                    }
                    if ($width > 1500) {
                        $srcset .= ', ' . $src . '/1500w 1500w';
                    }
                }
            }
        }

        return '<img' .
            " src=\"$src\"" .
            ' loading="lazy"' .
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

    private function getMediaNameFromPermalink(string $permalink): ?string
    {
        $blogUrl = $this->permalinkService->getBlogUrl($this->blog);
        $path = str_replace($blogUrl, '', $permalink);
        $path = trim($path, '/');
        $split = explode('/', $path);

        if ($split[0] !== 'media') {
            return null;
        }

        return $split[1] ?? null;
    }
}
