<?php

namespace App\Domains\Delivery\Processors\Sitemap;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\Content\ProsemirrorHelper;
use App\Domains\Route\PermalinkRepository;
use App\Models\Post;

class UrlPostEntry
{
    public function __construct(private Post $post)
    {
    }

    public function toXML()
    {
        $entry = new UrlEntry();
        $blog = $this->post->blog;

        foreach ($this->post->variants as $variant) {
            $url = PermalinkRepository::getPostPermalink(
                $this->post,
                $blog,
                $variant->language
            );

            if ($variant->language->is_primary) {
                // main URL
                $entry->loc($url);

                // images
                $images = ProsemirrorHelper::findBlocks($variant->content, 'image');
                foreach ($images as $image) {
                    $src = $image['attrs']['src'] ?? null;

                    if (! $src) {
                        continue;
                    }

                    // external images
                    if (! PermalinkRepository::isLinkInBlog($src, $blog)) {
                        continue;
                    }

                    $entry->image($src);
                }
            }

            if ($variant->status === PostStatusEnum::PUBLISHED) {
                $entry->langAlt($variant->language->code, $url);
            }
        }

        return $entry->toXML();
    }
}
