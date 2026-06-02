<?php

namespace App\Service\Delivery\Processor\Sitemap;

use App\Entity\Enum\PostVariantStatus;
use App\Entity\Post;
use App\Service\Route\PermalinkService;

class UrlPostEntry
{
    public function __construct(
        private Post $post,
        private PermalinkService $permalinkService,
    ) {}

    public function toXML(): string
    {
        $entry = new UrlEntry();
        $blog = $this->post->getBlog();

        foreach ($this->post->getVariants() as $variant) {
            $language = $variant->getLanguage();
            $url = $this->permalinkService->getPostPermalink($this->post, $blog, $language);

            if ($language->isPrimary()) {
                $entry->loc($url);

                $images = $this->findImages($variant->getContent());
                foreach ($images as $src) {
                    if ($this->permalinkService->isLinkInBlog($src, $blog)) {
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

    /** @return string[] */
    private function findImages(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        return $this->extractImageSrcs($data);
    }

    /**
     * @param array<mixed> $node
     * @return string[]
     */
    private function extractImageSrcs(array $node): array
    {
        $srcs = [];

        if (($node['type'] ?? null) === 'image') {
            $src = $node['attrs']['src'] ?? null;
            if (is_string($src)) {
                $srcs[] = $src;
            }
        }

        foreach ($node['content'] ?? [] as $child) {
            if (is_array($child)) {
                $srcs = array_merge($srcs, $this->extractImageSrcs($child));
            }
        }

        return $srcs;
    }
}
