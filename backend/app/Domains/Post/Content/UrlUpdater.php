<?php

namespace App\Domains\Post\Content;

use App\Domains\Post\Content\Marks\Link;
use App\Domains\Post\Content\Nodes\Audio\Audio;
use App\Domains\Post\Content\Nodes\Image\Image;
use App\Domains\Post\Content\Nodes\Text;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Document\Node;

class UrlUpdater
{

    public function __construct(
        private Node $document
    )
    {
    }

    public function updateFromOldToNew(
        string $oldUrl,
        string $newUrl,
        bool $updateMedia = true,
        bool $updateLinks = true
    ) : Node
    {
        return $this->update(
            function (Node $media) use ($oldUrl, $newUrl, $updateMedia) {

                if (!$updateMedia) {
                    return false;
                }

                $oldMediaPrefix = $oldUrl . '/media/';
                $src = $media->attrs->src;

                if (!$src) {
                    return false;
                }
                if (!str_starts_with($src, $oldMediaPrefix)) {
                    return false;
                }

                return $newUrl . '/media/' . substr($src, strlen($oldMediaPrefix));
            },
            function (Mark $link) use ($oldUrl, $newUrl, $updateLinks) {

                if (!$updateLinks) {
                    return false;
                }

                $href = $link->attrs->href;

                if (!$href) {
                    return false;
                }
                if (!str_starts_with($href, $oldUrl)) {
                    return false;
                }

                return $newUrl . substr($href, strlen($oldUrl));
            }
        );
    }

    /**
     * in callbacks, false = no update, string = new url
     *
     * @param callable(Node): false|string $mediaUpdater
     * @param callable(Mark): false|string $linkUpdater
     */
    public function update(
        callable $mediaUpdater = null,
        callable $linkUpdater = null,
    ) : Node
    {

        $this->document->traverse(function (Node $node) use ($mediaUpdater, $linkUpdater) {

            if (
                $node->isOfType(Image::class) ||
                $node->isOfType(Audio::class)
            ) {
                if (!$mediaUpdater) {
                    return;
                }
                $newUrl = $mediaUpdater($node);
                if (is_string($newUrl)) {
                    $node->attrs->set('src', $newUrl);
                }
            } else if ($node->isOfType(Text::class)) {
                if (!$linkUpdater) {
                    return;
                }
                foreach ($node->marks as $mark) {
                    if ($mark->isOfType(Link::class)) {
                        $newUrl = $linkUpdater($mark);
                        if (is_string($newUrl)) {
                            $mark->attrs->set('href', $newUrl);
                        }
                    }
                }
            }

        });

        return $this->document;

    }

}