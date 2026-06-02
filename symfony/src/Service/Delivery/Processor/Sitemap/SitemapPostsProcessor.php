<?php

namespace App\Service\Delivery\Processor\Sitemap;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Post;
use App\Service\Delivery\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Language\LanguageService;
use App\Service\Limit;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class SitemapPostsProcessor
{
    public function __construct(
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
    ) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $number = (int)$matchedRoute->param('number');
        if ($number < 1) {
            return null;
        }

        $posts = $this->getPosts($blog, $number);
        if (count($posts) === 0) {
            return null;
        }

        $parts = [];
        foreach ($posts as $post) {
            $entry = new UrlPostEntry($post, $this->permalinkService);
            $parts[] = $entry->toXML();
        }
        $postsXML = implode("\n", $parts);

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <urlset
            xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
            xmlns:xhtml="http://www.w3.org/1999/xhtml"
        >
            $postsXML
        </urlset>
        XML;

        return DeliveryResponse::forFile($xml, 'text/xml');
    }

    /** @return Post[] */
    private function getPosts(Blog $blog, int $number): array
    {
        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        $limit = Limit::MAX_ENTRIES_PER_SITEMAP;

        $qb = $this->em->createQueryBuilder();

        /** @var Post[] $posts */
        $posts = $qb->select('p')
            ->from(Post::class, 'p')
            ->join('p.variants', 'pv')
            ->where('p.blog_id = :blogId')
            ->andWhere('pv.language_id = :langId')
            ->andWhere('pv.status = :status')
            ->andWhere('p.is_page = false')
            ->setParameter('blogId', $blog->getId())
            ->setParameter('langId', $primaryLanguage->getId())
            ->setParameter('status', PostVariantStatus::PUBLISHED->value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult(($number - 1) * $limit)
            ->getQuery()
            ->getResult();

        return $posts;
    }
}
