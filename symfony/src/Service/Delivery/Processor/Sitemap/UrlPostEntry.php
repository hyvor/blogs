<?php

namespace App\Service\Delivery\Processor\Sitemap;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Post;
use App\Service\Post\Content\Nodes\Image\Image;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;

class UrlPostEntry
{
    public function __construct(
        private Post $post,
    ) {}

    public function toXML(
        PermalinkService $permalinkService,
        PostContentService $postContentService
    ): string
    {
        $entry = new UrlEntry();
        $blog = $this->post->getBlog();

        foreach ($this->post->getVariants() as $variant) {
            $language = $variant->getLanguage();
            $url = $permalinkService->getPostVariantPermalink($variant);

            if ($language->isPrimary()) {
                $entry->loc($url);

                $images = $this->findImageSrcs($variant->getContent(), $postContentService, $blog);
                foreach ($images as $src) {
                    if ($permalinkService->isLinkInBlog($src, $blog)) {
                        $entry->image($src);
                    }
                }
            }

            if ($variant->getStatus() === PostVariantStatus::PUBLISHED) {
                $entry->langAlt($language->getCode(), $url);
            }
        }

        return $entry->toXML();
    }

    /**
     * @return string[]
     */
    private function findImageSrcs(
        ?string $json,
        PostContentService $postContentService,
        Blog $blog,
    ): array
    {
        if (!$json) {
            return [];
        }

        try {
            $doc = $postContentService->getDocumentFromJson($json, $blog);

            $srcs = [];
            $doc->traverse(function (Node $node) use (&$srcs) {
                if ($node->isOfType(Image::class)) {
                    $src = $node->attrs->get('src');
                    if (is_string($src)) {
                        $srcs[] = $src;
                    }
                }
            });

            return $srcs;
        } catch (PhrosemirrorException) {
            return [];
        }
    }
}
