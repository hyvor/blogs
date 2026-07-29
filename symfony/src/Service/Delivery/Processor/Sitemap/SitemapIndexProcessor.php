<?php

namespace App\Service\Delivery\Processor\Sitemap;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Language;
use App\Entity\PostVariant;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Language\LanguageService;
use App\Service\Limit;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class SitemapIndexProcessor
{
    public function __construct(
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
    ) {}

    public function process(Blog $blog): ?DeliveryResponse
    {
        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        $postSitemaps = $this->getPostSitemaps($blog, $primaryLanguage);

        /** @var IndexEntry[] $sitemaps */
        $sitemaps = [new IndexEntry('sitemap-pages.xml'), ...$postSitemaps];

        $baseUrl = $this->permalinkService->getBlogUrl($blog);
        $sitemapsXml = '';

        $sitemapsLength = count($sitemaps);
        foreach ($sitemaps as $i => $sitemap) {
            $url = "$baseUrl/$sitemap->name";
            $sitemapsXml .= "<sitemap><loc>$url</loc></sitemap>";
            if ($i !== $sitemapsLength - 1) {
                $sitemapsXml .= "\n\t";
            }
        }

        $content = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            $sitemapsXml
        </sitemapindex>
        XML;

        return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $content, 'text/xml');
    }

    /** @return IndexEntry[] */
    private function getPostSitemaps(Blog $blog, Language $primaryLanguage): array
    {
        $qb = $this->em->createQueryBuilder();
        $count = (int)$qb->select('COUNT(pv.id)')
            ->from(PostVariant::class, 'pv')
            ->join('pv.post', 'p')
            ->where('p.blog = :blog')
            ->andWhere('pv.language = :lang')
            ->andWhere('pv.status = :status')
            ->andWhere('p.is_page = false')
            ->setParameter('blog', $blog)
            ->setParameter('lang', $primaryLanguage)
            ->setParameter('status', PostVariantStatus::PUBLISHED)
            ->getQuery()
            ->getSingleScalarResult();

        if ($count === 0) {
            return [];
        }

        $sitemapsCount = (int)ceil($count / Limit::MAX_ENTRIES_PER_SITEMAP);
        $result = [];
        foreach (range(1, $sitemapsCount) as $number) {
            $result[] = new IndexEntry("sitemap-posts-$number.xml");
        }
        return $result;
    }
}
