<?php declare(strict_types=1);

namespace App\Service\Post\Content;

use App\Service\Blog\UpdateBlogUrls\Updater\UpdaterInterface;
use App\Service\Post\Content\Marks\Link;
use App\Service\Post\Content\Nodes\Audio\Audio;
use App\Service\Post\Content\Nodes\Image\Image;
use App\Service\Post\Content\Nodes\Text;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Document\Node;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class DocUrlUpdater
{
    public function __construct(private Node $document) {}

    public function updateFromUpdater(
        UpdaterInterface $updater,
        bool $updateMedia = true,
        bool $updateLinks = true
    ): Node {
        return $this->update(
            function (Node $media) use ($updater, $updateMedia) {
                if (!$updateMedia) {
                    return false;
                }

                /** @var string $src */
                $src = $media->attrs->get('src', false);

                if (!$src) {
                    return false;
                }

                return $updater->update($src);
            },
            function (Mark $link) use ($updater, $updateLinks) {
                if (!$updateLinks) {
                    return false;
                }

                /** @var string $href */
                $href = $link->attrs->get('href', false);

                if (!$href) {
                    return false;
                }

                return $updater->update($href);
            }
        );
    }

    /**
     * in callbacks, false = no update, string = new url
     *
     * @param (callable(Node): (false|string))|null $mediaUpdater
     * @param (callable(Mark): (false|string))|null $linkUpdater
     */
    public function update(
        ?callable $mediaUpdater = null,
        ?callable $linkUpdater = null,
    ): Node {
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
            } elseif ($node->isOfType(Text::class)) {
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
