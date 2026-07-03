<?php

namespace App\Service\Blog\Hosting;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\HostingChanges;
use App\Message\HostingChangeMessage;
use App\Service\Blog\Event\BlogHostingChangedEvent;
use App\Service\Blog\Hosting\Exception\PendingHostingChangeException;
use App\Service\CustomDomain\CustomDomainService;
use App\Service\Route\PermalinkService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class HostingChangeService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
        private PermalinkService $permalinkService,
        private CustomDomainService $customDomainService,
        private EventDispatcherInterface $eventDispatcher,
        private UpdateBlogUrlsService $updateBlogUrlsService,
    ) {
    }

    /**
     * Creates a HostingChanges record capturing the requested transition, and dispatches
     * the async job that will actually apply it.
     */
    /**
     * @throws PendingHostingChangeException if the blog already has a change in progress
     */
    public function requestHostingChange(Blog $blog, BlogHostingAt $toAt, ?string $toHostingUrl = null): HostingChanges
    {
        if ($this->hasPendingChange($blog)) {
            throw new PendingHostingChangeException($blog);
        }

        $fromDomain = $blog->getCustomDomain()?->getDomain();
        $toDomain = $toAt === BlogHostingAt::DOMAIN ? $fromDomain : null;

        $hostingChange = new HostingChanges();
        $hostingChange->setBlog($blog);
        $hostingChange->setFromAt($blog->getHostingAt());
        $hostingChange->setFromDomain($fromDomain);
        $hostingChange->setFromUrl($this->permalinkService->getBlogUrl($blog));
        $hostingChange->setToAt($toAt);
        $hostingChange->setToDomain($toDomain);
        $hostingChange->setToUrl($this->permalinkService->buildUrlForHosting($blog, $toAt, $toHostingUrl, $toDomain));
        $hostingChange->setStatus(HostingChangeStatus::CHANGING);
        $hostingChange->setCreatedAt($this->now());
        $hostingChange->setUpdatedAt($this->now());

        $this->em->persist($hostingChange);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            // guards against a race between the hasPendingChange() check above and this insert
            throw new PendingHostingChangeException($blog);
        }

        $this->bus->dispatch(new HostingChangeMessage($hostingChange->getId()));

        return $hostingChange;
    }

    public function hasPendingChange(Blog $blog): bool
    {
        $latest = $this->getLatestChange($blog);
        return $latest !== null && $latest->getStatus() === HostingChangeStatus::CHANGING;
    }

    public function getLatestChange(Blog $blog): ?HostingChanges
    {
        /** @var HostingChanges|null */
        return $this->em->createQueryBuilder()
            ->select('hc')
            ->from(HostingChanges::class, 'hc')
            ->where('hc.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('hc.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Applies a hosting change within a transaction. On failure, the exception is left to
     * propagate; HostingChangeMessageHandler decides whether to retry or mark it as failed.
     *
     * NOTE: Doctrine closes the EntityManager on any exception raised inside
     * wrapInTransaction() — the caller must obtain a fresh one before using it again.
     */
    public function process(HostingChanges $hostingChange): void
    {
        $this->em->wrapInTransaction(function () use ($hostingChange) {
            $this->applyChange($hostingChange);
        });

        $hostingChange->setStatus(HostingChangeStatus::SUCCESS);
        $hostingChange->setUpdatedAt($this->now());
        $this->em->flush();

        $this->eventDispatcher->dispatch(new BlogHostingChangedEvent($hostingChange));
    }

    private function applyChange(HostingChanges $hostingChange): void
    {
        $blog = $hostingChange->getBlog();
        $toAt = $hostingChange->getToAt();

        $this->updateBlogUrlsService->updateUrls(
            $blog,
            $hostingChange->getFromUrl() ?? '',
            $hostingChange->getToUrl() ?? ''
        );

        if ($blog->getHostingAt() === BlogHostingAt::DOMAIN && $toAt !== BlogHostingAt::DOMAIN && $blog->getCustomDomain()) {
            $this->customDomainService->deleteCustomDomain($blog->getCustomDomain(), flush: false);
            $blog->setCustomDomain(null);
        }

        $blog->setHostingAt($toAt);
        $blog->setHostingUrl($toAt === BlogHostingAt::SELF ? $hostingChange->getToUrl() : null);

        $this->em->flush();
    }
}
