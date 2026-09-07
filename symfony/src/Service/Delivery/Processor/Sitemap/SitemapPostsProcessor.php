<?php

namespace App\Service\Delivery\Processor\Sitemap;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Post;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Language\LanguageService;
use App\Service\Limit;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class SitemapPostsProcessor
{
    public function __construct(
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
        private PostContentService $postContentService
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
            $entry = new UrlPostEntry($post);
            $parts[] = $entry->toXML($this->permalinkService, $this->postContentService);
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

        return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $xml, 'text/xml');
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
            ->addSelect('pv')
            ->where('p.blog = :blog')
            ->andWhere('pv.language = :lang')
            ->andWhere('pv.status = :status')
            ->andWhere('p.is_page = false')
            ->setParameter('blog', $blog)
            ->setParameter('lang', $primaryLanguage)
            ->setParameter('status', PostVariantStatus::PUBLISHED)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult(($number - 1) * $limit)
            ->getQuery()
            ->getResult();

        return $posts;
    }
}
