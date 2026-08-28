<?php

namespace App\Service\LinkAnalysis;

use App\Entity\Blog;
use App\Entity\Enum\LinkAnalyzerLinkStatus;
use App\Entity\LinkAnalyzerCheck;
use App\Entity\LinkAnalyzerLink;
use App\Entity\PostVariant;
use App\Service\LinkAnalysis\Exception\LinkAnalysisCheckAlreadyPendingException;
use App\Service\LinkAnalysis\Message\LinkAnalysisCheckMessage;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Blog-level link analysis orchestration: reads, ignoring links, and starting checks.
 * All persistence is delegated to LinkAnalyzerRepository.
 */
class LinkAnalysisService
{
    public const int IGNORE_CODE = -2;

    public function __construct(
        private LinkAnalyzerRepository $linkAnalyzerRepository,
        private MessageBusInterface $bus,
    ) {}

    /**
     * @return array{ok: int, redirect: int, broken: int, risky: int, ignored: int}
     */
    public function getCountsByStatus(Blog $blog): array
    {
        return $this->linkAnalyzerRepository->getLinkCountsByStatus($blog);
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
        return $this->linkAnalyzerRepository->findLinks($blog, $status, $postVariantId, $limit, $offset);
    }

    /**
     * @return LinkAnalyzerCheck[]
     */
    public function getChecks(Blog $blog, int $limit, int $offset): array
    {
        return $this->linkAnalyzerRepository->findChecks($blog, $limit, $offset);
    }

    public function getLastCheck(Blog $blog): ?LinkAnalyzerCheck
    {
        return $this->linkAnalyzerRepository->findLastCheck($blog);
    }

    /**
     * @throws LinkAnalysisCheckAlreadyPendingException
     */
    public function createCheck(Blog $blog): LinkAnalyzerCheck
    {
        if ($this->linkAnalyzerRepository->findPendingCheck($blog) !== null) {
            throw new LinkAnalysisCheckAlreadyPendingException(
                'Link analysis check is already pending for this blog'
            );
        }

        $check = $this->linkAnalyzerRepository->createCheck($blog);

        $this->bus->dispatch(new LinkAnalysisCheckMessage($check->getId()));

        return $check;
    }

    public function getLink(PostVariant $postVariant, string $url): ?LinkAnalyzerLink
    {
        return $this->linkAnalyzerRepository->findLink($postVariant, $url);
    }

    public function ignoreLink(LinkAnalyzerLink $link, bool $status): void
    {
        $this->linkAnalyzerRepository->setLinkIgnored($link, $status);
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
