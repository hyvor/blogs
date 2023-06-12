<?php

namespace App\Domains\Post\Content\_Nodes;

use App\Domains\Media\Image\ImageResizeService;
use App\Domains\Media\MediaRepository;
use App\Domains\Route\PermalinkRepository;
use App\Helpers\MimeTypes;
use App\Models\Blog;
use Tiptap\Core\Node;

class Image extends Node
{
    public static $name = 'image';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'img[src]',
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'src' => null,
            'alt' => null,
            'width' => null,
            'height' => null,
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {

        /** @var Blog $blog */
        $blog = $this->options['blog'];

        $src = htmlspecialchars($node->attrs->src ?? '');
        $srcset = null;

        $mediaName = PermalinkRepository::getMediaNameFromPermalink($blog, $src);

        if (
            $mediaName &&
            ($media = MediaRepository::getByBlogIdAndName($blog->id, $mediaName))
        ) {

            $mimeType = MimeTypes::getMimeFromExtension($media->extension);

            if (ImageResizeService::isMimeTypeSupported($mimeType)) {

                $width = ImageResizeService::getImageWidth(MediaRepository::getContents($media));

                $srcset = $src . ' ' . $width . 'w';

                if ($width > 500) $srcset .= ', ' . $src . '/500w 500w';
                if ($width > 750) $srcset .= ', ' . $src . '/750w 750w';
                if ($width > 1000) $srcset .= ', ' . $src . '/1000w 1000w';
                if ($width > 1500) $srcset .= ', ' . $src . '/1500w 1500w';

            }

        }

        return ['img', array_merge($HTMLAttributes, [
            'srcset' => $srcset
        ]), 0];
    }
}
