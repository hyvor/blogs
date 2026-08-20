<?php

namespace App\Service\LinkAnalysis;

use App\Entity\Blog;
use App\Entity\Enum\JobStatus;
use App\Entity\Enum\LinkAnalyzerLinkStatus;
use App\Entity\LinkAnalyzerCheck;
use App\Entity\LinkAnalyzerLink;
use App\Entity\PostVariant;
use App\Service\LinkAnalysis\Dto\AnalyzedLink;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

/**
 * All database access for LinkAnalyzerLink and LinkAnalyzerCheck entities.
 */
class LinkAnalyzerRepository
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    // -----------------------------------------------------------------
    // LinkAnalyzerLink
    // -----------------------------------------------------------------

    /**
     * @return array{ok: int, redirect: int, broken: int, risky: int, ignored: int}
     */
    public function getLinkCountsByStatus(Blog $blog): array
    {
        $conn = $this->em->getConnection();
        $result = $conn->executeQuery(
            '
            SELECT
                SUM(CASE WHEN ignore = false AND status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) AS ok,
                SUM(CASE WHEN ignore = false AND status_code >= 300 AND status_code < 400 THEN 1 ELSE 0 END) AS redirect,
                SUM(CASE WHEN ignore = false AND (status_code = 404 OR status_code = 0) THEN 1 ELSE 0 END) AS broken,
                SUM(CASE WHEN ignore = false AND (status_code != 404 AND status_code != 0 AND (status_code >= 400 OR status_code < 200)) THEN 1 ELSE 0 END) AS risky,
                SUM(CASE WHEN ignore = true THEN 1 ELSE 0 END) AS ignored
            FROM link_analyzer_links
            WHERE blog_id = :blogId
            ',
            ['blogId' => $blog->getId()],
        )->fetchAssociative();

        /** @var array<string, string|int|null> $result */
        $result = $result === false ? [] : $result;

        return [
            'ok' => (int)($result['ok'] ?? 0),
            'redirect' => (int)($result['redirect'] ?? 0),
            'broken' => (int)($result['broken'] ?? 0),
            'risky' => (int)($result['risky'] ?? 0),
            'ignored' => (int)($result['ignored'] ?? 0),
        ];
    }

    /**
     * @return LinkAnalyzerLink[]
     */
    public function findLinks(
        Blog $blog,
        ?LinkAnalyzerLinkStatus $status,
        ?int $postVariantId,
        int $limit,
        int $offset
    ): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select(
                'l',
                'pv',
                'lang',
                'CASE WHEN l.ignore = false AND l.status_code >= 200 AND l.status_code < 300 THEN 1 ELSE 0 END AS HIDDEN ok',
                'CASE WHEN l.ignore = false AND l.status_code >= 300 AND l.status_code < 400 THEN 1 ELSE 0 END AS HIDDEN redirect',
                'CASE WHEN l.ignore = false AND (l.status_code = 404 OR l.status_code = 0) THEN 1 ELSE 0 END AS HIDDEN broken',
                'CASE WHEN l.ignore = false AND l.status_code != 404 AND l.status_code != 0 AND (l.status_code < 200 OR l.status_code >= 400) THEN 1 ELSE 0 END AS HIDDEN risky',
                'CASE WHEN l.ignore = true THEN 1 ELSE 0 END AS HIDDEN ignored'
            )
            ->from(LinkAnalyzerLink::class, 'l')
            ->leftJoin('l.post_variant', 'pv')
            ->leftJoin('pv.language', 'lang')
            ->where('l.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('broken', 'DESC')
            ->addOrderBy('risky', 'DESC')
            ->addOrderBy('redirect', 'DESC')
            ->addOrderBy('l.last_checked_at', 'DESC')
            ->addOrderBy('l.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($postVariantId !== null) {
            $qb->andWhere('l.post_variant = :postVariantId')
                ->setParameter('postVariantId', $postVariantId);
        }

        match ($status) {
            LinkAnalyzerLinkStatus::OK => $qb
                ->andWhere('l.ignore = false')
                ->andWhere('l.status_code >= 200')
                ->andWhere('l.status_code < 300'),
            LinkAnalyzerLinkStatus::REDIRECT => $qb
                ->andWhere('l.ignore = false')
                ->andWhere('l.status_code >= 300')
                ->andWhere('l.status_code < 400'),
            LinkAnalyzerLinkStatus::BROKEN => $qb
                ->andWhere('l.ignore = false')
                ->andWhere('l.status_code = 404 OR l.status_code = 0'),
            LinkAnalyzerLinkStatus::RISKY => $qb
                ->andWhere('l.ignore = false')
                ->andWhere('l.status_code != 404')
                ->andWhere('l.status_code != 0')
                ->andWhere('l.status_code < 200 OR l.status_code >= 400'),
            LinkAnalyzerLinkStatus::IGNORED => $qb
                ->andWhere('l.ignore = true'),
            default => null,
        };

        /** @var LinkAnalyzerLink[] $result */
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function findLink(PostVariant $postVariant, string $url): ?LinkAnalyzerLink
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('l')
            ->from(LinkAnalyzerLink::class, 'l')
            ->where('l.post_variant_id = :postVariantId')
            ->andWhere('l.url = :url')
            ->setParameter('postVariantId', $postVariant->getId())
            ->setParameter('url', $url);

        /** @var LinkAnalyzerLink|null $result */
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    public function setLinkIgnored(LinkAnalyzerLink $link, bool $status): void
    {
        $link->setIgnore($status);
        $this->em->flush();
    }

    /**
     * @return string[]
     */
    public function findIgnoredLinkUrls(PostVariant $variant): array
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
     * Persists analyzed links for a post variant, creating or updating rows as needed.
     *
     * Existing links are batch-loaded in a single query and the whole batch is
     * flushed once at the end, instead of issuing one SELECT and one flush per link.
     *
     * @param AnalyzedLink[] $results
     * @param string[] $ignoreUrls
     * @return LinkAnalyzerLink[]
     */
    public function syncLinksForVariant(
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

        // nothing can remain after a clear, so skip the lookup query entirely
        $existingLinksByUrl = $shouldClear ? [] : $this->findLinksIndexedByUrl($variant);

        $links = [];

        foreach ($results as $result) {
            $link = $existingLinksByUrl[$result->originalUrl] ?? null;

            if ($link === null) {
                $link = new LinkAnalyzerLink();
                $link->setBlog($blog);
                $link->setPostVariant($variant);
                $link->setUrl($result->originalUrl);
                $link->setCreatedAt($now);

                $this->em->persist($link);
            }

            $shouldIgnore = in_array($result->originalUrl, $ignoreUrls, true) || $result->status->ignored;

            $link->setFullUrl($result->url);
            $link->setStatusCode($result->status->httpStatus);
            $link->setIgnore($shouldIgnore);

            $link->setCheckType($result->status->type);
            $link->setIgnoreReason($shouldIgnore ? $result->status->ignoreReason?->value : null);
            $link->setComment($result->status->comment);

            $link->setUpdatedAt($now);
            $link->setLastCheckedAt($now);

            $links[] = $link;
        }

        $this->em->flush();

        return $links;
    }

    /**
     * @return array<string, LinkAnalyzerLink> Existing links for the variant, indexed by URL
     */
    private function findLinksIndexedByUrl(PostVariant $variant): array
    {
        /** @var LinkAnalyzerLink[] $links */
        $links = $this->em->createQueryBuilder()
            ->select('l')
            ->from(LinkAnalyzerLink::class, 'l')
            ->where('l.post_variant = :variant')
            ->setParameter('variant', $variant)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($links as $link) {
            $indexed[$link->getUrl()] = $link;
        }
        return $indexed;
    }

    // -----------------------------------------------------------------
    // LinkAnalyzerCheck
    // -----------------------------------------------------------------

    /**
     * @return LinkAnalyzerCheck[]
     */
    public function findChecks(Blog $blog, int $limit, int $offset): array
    {
        /** @var LinkAnalyzerCheck[] $result */
        $result = $this->em->createQueryBuilder()
            ->select('c')
            ->from(LinkAnalyzerCheck::class, 'c')
            ->where('c.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
        return $result;
    }

    public function findLastCheck(Blog $blog): ?LinkAnalyzerCheck
    {
        /** @var LinkAnalyzerCheck|null $result */
        $result = $this->em->createQueryBuilder()
            ->select('c')
            ->from(LinkAnalyzerCheck::class, 'c')
            ->where('c.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        return $result;
    }

    /**
     * The latest check for each of the given blogs, indexed by blog ID.
     *
     * Used to avoid an N+1 query when checking many blogs at once
     * (e.g. one query per blog to find its last check).
     *
     * @param int[] $blogIds
     * @return array<int, LinkAnalyzerCheck>
     */
    public function findLastChecksByBlogIds(array $blogIds): array
    {
        if ($blogIds === []) {
            return [];
        }

        /** @var array<array{blogId: int, maxId: int}> $latest */
        $latest = $this->em->createQueryBuilder()
            ->select('IDENTITY(c.blog) AS blogId', 'MAX(c.id) AS maxId')
            ->from(LinkAnalyzerCheck::class, 'c')
            ->where('c.blog IN (:blogIds)')
            ->setParameter('blogIds', $blogIds)
            ->groupBy('c.blog')
            ->getQuery()
            ->getArrayResult();

        if ($latest === []) {
            return [];
        }

        $ids = array_map(fn(array $row) => (int)$row['maxId'], $latest);

        /** @var LinkAnalyzerCheck[] $checks */
        $checks = $this->em->createQueryBuilder()
            ->select('c')
            ->from(LinkAnalyzerCheck::class, 'c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $byBlogId = [];
        foreach ($checks as $check) {
            $byBlogId[$check->getBlog()->getId()] = $check;
        }
        return $byBlogId;
    }

    public function findPendingCheck(Blog $blog): ?LinkAnalyzerCheck
    {
        /** @var LinkAnalyzerCheck|null $result */
        $result = $this->em->createQueryBuilder()
            ->select('c')
            ->from(LinkAnalyzerCheck::class, 'c')
            ->where('c.blog = :blog')
            ->andWhere('c.status = :status')
            ->setParameter('blog', $blog)
            ->setParameter('status', JobStatus::PENDING)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        return $result;
    }

    public function createCheck(Blog $blog): LinkAnalyzerCheck
    {
        $check = new LinkAnalyzerCheck();
        $check->setBlog($blog);
        $check->setCreatedAt($this->now());
        $check->setStatus(JobStatus::PENDING);

        $this->em->persist($check);
        $this->em->flush();

        return $check;
    }
}
