<?php

namespace App\Service\LinkAnalysis;

use App\Entity\Blog;
use App\Entity\LinkAnalyzerLink;
use App\Entity\PostVariant;
use App\Service\LinkAnalysis\StatusCheck\AnalyzedLink;
use App\Service\Post\PostService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class PostVariantLinkService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private PostService $postService,
    )
    {
    }

    /**
     * @return string[]
     */
    public function getIgnoredLinks(PostVariant $variant): array
    {
        /** @var LinkAnalyzerLink[] $links */
        $links = $this->em->createQueryBuilder()
            ->select('l')
            ->from(LinkAnalyzerLink::class, 'l')
            ->where('l.post_variant = :variant AND l.ignore = true')
            ->setParameter('variant', $variant)
            ->getQuery()
            ->getResult();

        return array_map(fn(LinkAnalyzerLink $link) => $link->getUrl(), $links);
    }

    /**
     * @param AnalyzedLink[] $results
     * @param string[] $ignoreUrls
     * @return LinkAnalyzerLink[]
     */
    public function updateLinksFromResults(
        Blog $blog,
        PostVariant $variant,
        array $results,
        bool $shouldClear = false,
        array $ignoreUrls = [],
    ): array {
        $now = $this->now();

        if ($shouldClear) {
            $this->em->createQueryBuilder()
                ->delete(LinkAnalyzerLink::class, 'l')
                ->where('l.post_variant = :variant')
                ->setParameter('variant', $variant)
                ->getQuery()
                ->execute();
        }

        $links = [];

        foreach ($results as $result) {
            /** @var LinkAnalyzerLink|null $link */
            $link = $this->em->createQueryBuilder()
                ->select('l')
                ->from(LinkAnalyzerLink::class, 'l')
                ->where('l.post_variant = :variant AND l.url = :url')
                ->setParameter('variant', $variant)
                ->setParameter('url', $result->originalUrl)
                ->getQuery()
                ->getOneOrNullResult();


            if ($link === null) {
                $link = new LinkAnalyzerLink();
                $link->setBlog($blog);
                $link->setPostVariant($variant);
                $link->setUrl($result->originalUrl);
                $link->setCreatedAt($now);

                $this->em->persist($link);
            }

            $shouldIgnore = in_array($result->originalUrl, $ignoreUrls) || $result->status->ignored;

            $link->setFullUrl($result->url);
            $link->setStatusCode($result->status->httpStatus);
            $link->setIgnore($shouldIgnore);

            $link->setCheckType($result->status->type);
            $link->setIgnoreReason($shouldIgnore ? $result->status->ignoreReason?->value : null);
            $link->setComment($result->status->comment);

            $link->setUpdatedAt($now);
            $link->setLastCheckedAt($now);

            $this->em->flush();

            $links[] = $link;
        }

        return $links;
    }

    /**
     * @param array<string, number> $results
     */
    public function updatePostVariantCache(
        PostVariant $variant,
        array $results,
        bool $append = false
    ): void
    {
        $currentVariantResult = $variant->getLinkAnalysis() ?? [];
        $blog = $variant->getPost()->getBlog();

        $this->postService->updatePostVariant(
            $variant,
            $blog,
            [
                'link_analysis' => $append ? array_merge(
                    $currentVariantResult,
                    $results
                ) : $results
            ]
        );
    }
}