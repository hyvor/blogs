<?php

namespace App\Service\LinkAnalysis;

use App\Entity\Blog;
use App\Entity\Enum\JobStatus;
use App\Entity\Enum\LinkAnalyzerLinkStatus;
use App\Entity\LinkAnalyzerCheck;
use App\Entity\LinkAnalyzerLink;
use App\Entity\PostVariant;
use App\Message\LinkAnalysisCheckMessage;
use App\Service\LinkAnalysis\Exception\LinkAnalysisCheckAlreadyPendingException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class LinkAnalysisService
{
    use ClockAwareTrait;

    public const int IGNORE_CODE = -2;

    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
    ) {}

    /**
     * @return array{ok: int, redirect: int, broken: int, risky: int, ignored: int}
     */
    public function getCountsByStatus(Blog $blog): array
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
    public function getLinks(
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

    /**
     * @return LinkAnalyzerCheck[]
     */
    public function getChecks(Blog $blog, int $limit, int $offset): array
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

    public function getLastCheck(Blog $blog): ?LinkAnalyzerCheck
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
     * @throws LinkAnalysisCheckAlreadyPendingException
     */
    public function createCheck(Blog $blog): LinkAnalyzerCheck
    {
        $pending = $this->em->createQueryBuilder()
            ->select('c')
            ->from(LinkAnalyzerCheck::class, 'c')
            ->where('c.blog = :blog')
            ->andWhere('c.status = :status')
            ->setParameter('blog', $blog)
            ->setParameter('status', JobStatus::PENDING)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($pending !== null) {
            throw new LinkAnalysisCheckAlreadyPendingException(
                'Link analysis check is already pending for this blog'
            );
        }

        $check = new LinkAnalyzerCheck();
        $check->setBlog($blog);
        $check->setCreatedAt($this->now());
        $check->setStatus(JobStatus::PENDING);

        $this->em->persist($check);
        $this->em->flush();

        $this->bus->dispatch(new LinkAnalysisCheckMessage($check->getId()));

        return $check;
    }

    public function getLink(PostVariant $postVariant, string $url): ?LinkAnalyzerLink
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

    public function ignoreLink(LinkAnalyzerLink $link, bool $status): void
    {
        $link->setIgnore($status);
        $this->em->flush();
    }

    /**
     * @param LinkAnalyzerLink[] $links
     * @return array<string, int>
     */
    public static function getIgnoreAwareStatusFromLinks(array $links): array
    {
        $results = [];

        foreach ($links as $link) {
            $results[$link->getUrl()] = $link->isIgnore() ? self::IGNORE_CODE : $link->getStatusCode();
        }

        return $results;
    }
}
