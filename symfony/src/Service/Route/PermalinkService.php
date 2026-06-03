<?php

namespace App\Service\Route;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\Route;
use App\Service\AppConfig;
use Doctrine\ORM\EntityManagerInterface;

class PermalinkService
{
    public function __construct(
        private AppConfig $appConfig,
        private EntityManagerInterface $em,
    ) {}

    public function getBlogUrl(Blog $blog): string
    {
        return match ($blog->getHostingAt()) {
            BlogHostingAt::SUBDOMAIN => $this->buildSubdomainUrl($blog),
            BlogHostingAt::DOMAIN => 'https://' . $blog->getHostingDomain(),
            BlogHostingAt::SELF => $blog->getHostingUrl() ?? '',
        };
    }

    public function getBlogPermalink(Blog $blog, Language $language): string
    {
        $base = $this->getBlogUrl($blog);
        return $language->isPrimary() ? $base : $base . '/' . $language->getCode();
    }

    public function getPostPermalink(Post $post, Blog $blog, Language $language): string
    {
        $routeName = $post->isPage() ? 'page' : 'post';
        $route = $this->em->getRepository(Route::class)->findOneBy([
            'blog' => $blog,
            'name' => $routeName,
        ]);

        $match = $route ? $route->getMatch() : '/{slug}';

        $variant = null;
        foreach ($post->getVariants() as $v) {
            if ($v->getLanguageId() === $language->getId()) {
                $variant = $v;
                break;
            }
        }
        $variant ??= $post->getVariants()->first() ?: null;

        $slug = $variant?->getSlug() ?? '';
        $path = str_replace('{slug}', $slug, $match);

        if (!$language->isPrimary()) {
            $path = '/' . $language->getCode() . $path;
        }

        return $this->getBlogUrl($blog) . $path;
    }

    public function isLinkInBlog(string $link, Blog $blog): bool
    {
        return str_starts_with($link, $this->getBlogUrl($blog));
    }

    private function buildSubdomainUrl(Blog $blog): string
    {
        $url = $this->appConfig->getDeliveryUrl();
        $scheme = parse_url($url, PHP_URL_SCHEME) ?? 'https';
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $port = parse_url($url, PHP_URL_PORT);
        $portStr = $port ? ":$port" : '';
        return "$scheme://{$blog->getSubdomain()}.$host$portStr";
    }
}
