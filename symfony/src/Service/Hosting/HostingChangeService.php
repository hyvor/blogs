<?php

namespace App\Service\Hosting;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\HostingChange;
use App\Service\Blog\Event\BlogHostingChangedEvent;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlEvent;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessage;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessageHandler;
use App\Service\Hosting\CustomDomain\CustomDomainIntentService;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Service\Hosting\Exception\PendingHostingChangeException;
use App\Service\Hosting\Message\HostingChangeMessage;
use App\Service\Route\PermalinkService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
        private CustomDomainIntentService $customDomainIntentService,
        private EventDispatcherInterface $eventDispatcher,
        private UpdateBlogUrlsMessageHandler $updateBlogUrlsMessageHandler,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Creates the HostingChange record and dispatches the job to handle it asynchronously.
     * @throws PendingHostingChangeException if the blog already has a change in progress
     */
    public function startHostingChange(
        Blog $blog,
        BlogHostingAt $toAt,
        ?string $toSubdomain = null,
        ?string $toHostingUrl = null,
        ?string $toDomain = null
    ): HostingChange
    {

        match ($toAt) {
            BlogHostingAt::SUBDOMAIN => assert($toSubdomain !== null, 'toSubdomain must be provided when changing to SUBDOMAIN'),
            BlogHostingAt::SELF => assert($toHostingUrl !== null, 'toHostingUrl must be provided when changing to SELF'),
            BlogHostingAt::DOMAIN => assert($toDomain !== null, 'toDomain must be provided when changing to DOMAIN'),
        };

        if ($this->hasPendingChange($blog)) {
            throw new PendingHostingChangeException($blog);
        }

        $hostingChange = new HostingChange();
        $hostingChange->setBlog($blog);
        $hostingChange->setStatus(HostingChangeStatus::CHANGING);
        $hostingChange->setCreatedAt($this->now());
        $hostingChange->setUpdatedAt($this->now());

        $hostingChange->setFromAt($blog->getHostingAt());
        if ($hostingChange->getFromAt() === BlogHostingAt::SUBDOMAIN) {
            assert($blog->getSubdomain());
            $hostingChange->setFromSubdomain($blog->getSubdomain());
        } elseif ($hostingChange->getFromAt() === BlogHostingAt::SELF) {
            assert($blog->getHostingUrl());
            $hostingChange->setFromHostingUrl($blog->getHostingUrl());
        } elseif ($hostingChange->getFromAt() === BlogHostingAt::DOMAIN) {
            assert($blog->getCustomDomain()?->getDomain());
            $hostingChange->setFromDomain($blog->getCustomDomain()?->getDomain());
        }

        $hostingChange->setToAt($toAt);
        if ($toAt === BlogHostingAt::SUBDOMAIN) {
            assert($toSubdomain !== null);
            $hostingChange->setToSubdomain($toSubdomain);
        } elseif ($toAt === BlogHostingAt::SELF) {
            assert($toHostingUrl !== null);
            $hostingChange->setToHostingUrl($toHostingUrl);
        } elseif ($toAt === BlogHostingAt::DOMAIN) {
            assert($toDomain !== null);
            $hostingChange->setToDomain($toDomain);
        }

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
        return $this->em->createQueryBuilder()
            ->select('COUNT(hc.id)')
            ->from(HostingChange::class, 'hc')
            ->where('hc.blog = :blog')
            ->andWhere('hc.status = :status')
            ->setParameter('blog', $blog)
            ->setParameter('status', HostingChangeStatus::CHANGING)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function getLatestChange(Blog $blog): ?HostingChange
    {
        /** @var HostingChange|null */
        return $this->em->createQueryBuilder()
            ->select('hc')
            ->from(HostingChange::class, 'hc')
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
    public function process(HostingChange $hostingChange): void
    {
        $this->em->wrapInTransaction(function () use ($hostingChange) {
            $blog = $hostingChange->getBlog();
            $toAt = $hostingChange->getToAt();

            $fromUrl = $this->permalinkService->buildUrlForHosting(
                $hostingChange->getFromAt(),
                $hostingChange->getFromSubdomain(),
                $hostingChange->getFromHostingUrl(),
                $hostingChange->getFromDomain()
            );

            $toUrl = $this->permalinkService->buildUrlForHosting(
                $hostingChange->getToAt(),
                $hostingChange->getToSubdomain(),
                $hostingChange->getToHostingUrl(),
                $hostingChange->getToDomain()
            );

            $this->handleUpdateBlogUrls(
                $blog,
                $fromUrl,
                $toUrl
            );

            $blog->setHostingAt($toAt);
            $blog->setHostingUrl($toAt === BlogHostingAt::SELF ? $hostingChange->getToHostingUrl() : null);

            if ($toAt === BlogHostingAt::SUBDOMAIN) {
                assert($hostingChange->getToSubdomain() !== null);
                $blog->setSubdomain($hostingChange->getToSubdomain());
            }

            if ($toAt === BlogHostingAt::DOMAIN) {
                $intent = $this->customDomainIntentService->getBlogCustomDomainIntent($blog);
                assert($intent !== null, 'CustomDomainIntent must exist when a hosting change targets DOMAIN');
                $this->customDomainService->promoteIntentToCustomDomain($intent, flush: false);
            } elseif ($hostingChange->getFromAt() === BlogHostingAt::DOMAIN) {
                $existingCustomDomain = $blog->getCustomDomain();
                if ($existingCustomDomain !== null) {
                    $this->customDomainService->deleteCustomDomain($existingCustomDomain, flush: false);
                    $blog->setCustomDomain(null);
                }
            }

            $hostingChange->setStatus(HostingChangeStatus::SUCCESS);
            $hostingChange->setUpdatedAt($this->now());
        });

        $this->eventDispatcher->dispatch(new BlogHostingChangedEvent($hostingChange));
    }

    private function handleUpdateBlogUrls(
        Blog $blog,
        string $fromUrl,
        string $toUrl
    ): void
    {
        $message = new UpdateBlogUrlsMessage(
            $blog->getId(),
            UpdateBlogUrlEvent::HOSTING_CHANGED,
            lockKeys: [],
            blogOldUrl: $fromUrl,
            blogNewUrl: $toUrl
        );

        ($this->updateBlogUrlsMessageHandler)($message);
    }

}
