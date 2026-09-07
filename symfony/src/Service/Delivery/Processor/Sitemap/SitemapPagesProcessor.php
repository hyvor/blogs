<?php

namespace App\Service\Delivery\Processor\Sitemap;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Language;
use App\Entity\Post;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Language\LanguageService;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class SitemapPagesProcessor
{
    public function __construct(
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private PostContentService $postContentService,
        private EntityManagerInterface $em,
    ) {}

    public function process(Blog $blog): ?DeliveryResponse
    {
        $languages = $this->languageService->getAllLanguages($blog);
        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);

        $indexXML = $this->indexXML($blog, $languages);
        $pagesXML = $this->pagesXML($blog, $primaryLanguage);

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

        return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $xml, 'text/xml');
    }

    /** @param Language[] $languages */
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

    private function pagesXML(Blog $blog, Language $primaryLanguage): string
    {
        $qb = $this->em->createQueryBuilder();

        /** @var Post[] $posts */
        $posts = $qb->select('p')
            ->from(Post::class, 'p')
            ->join('p.variants', 'pv')
            ->where('p.blog = :blog')
            ->andWhere('pv.language = :lang')
            ->andWhere('pv.status = :status')
            ->andWhere('p.is_page = true')
            ->setParameter('blog', $blog)
            ->setParameter('lang', $primaryLanguage)
            ->setParameter('status', PostVariantStatus::PUBLISHED)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();

        $parts = [];
        foreach ($posts as $post) {
            $entry = new UrlPostEntry($post);
            $parts[] = $entry->toXML($this->permalinkService, $this->postContentService);
        }
        return implode("\n", $parts);
    }
}
