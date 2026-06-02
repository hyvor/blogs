<?php

namespace App\Service\Delivery\Processor\Sitemap;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Delivery\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Language\LanguageService;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class SitemapPagesProcessor
{
    public function __construct(
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
    ) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $languages = $this->languageService->getAllLanguages($blog);
        $primaryLanguage = null;
        foreach ($languages as $lang) {
            if ($lang->isPrimary()) {
                $primaryLanguage = $lang;
                break;
            }
        }

        $indexXML = $this->indexXML($blog, $languages);
        $pagesXML = $primaryLanguage ? $this->pagesXML($blog, $primaryLanguage) : '';

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <urlset
            xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
            xmlns:xhtml="http://www.w3.org/1999/xhtml"
        >
            $indexXML
            $pagesXML
        </urlset>
        XML;

        return DeliveryResponse::forFile($xml, 'text/xml');
    }

    /** @param \App\Entity\Language[] $languages */
    private function indexXML(Blog $blog, array $languages): string
    {
        $entry = new UrlEntry();
        foreach ($languages as $language) {
            $url = $this->permalinkService->getBlogPermalink($blog, $language);
            if ($language->isPrimary()) {
                $entry->loc($url);
            }
            $entry->langAlt($language->getCode(), $url);
        }
        return $entry->toXML();
    }

    private function pagesXML(Blog $blog, \App\Entity\Language $primaryLanguage): string
    {
        $qb = $this->em->createQueryBuilder();

        /** @var Post[] $posts */
        $posts = $qb->select('p')
            ->from(Post::class, 'p')
            ->join('p.variants', 'pv')
            ->where('p.blog_id = :blogId')
            ->andWhere('pv.language_id = :langId')
            ->andWhere('pv.status = :status')
            ->andWhere('p.is_page = true')
            ->setParameter('blogId', $blog->getId())
            ->setParameter('langId', $primaryLanguage->getId())
            ->setParameter('status', PostVariantStatus::PUBLISHED->value)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();

        $parts = [];
        foreach ($posts as $post) {
            $entry = new UrlPostEntry($post, $this->permalinkService);
            $parts[] = $entry->toXML();
        }
        return implode("\n", $parts);
    }
}
