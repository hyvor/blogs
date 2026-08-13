<?php

namespace App\Service\LinkAnalysis\MessageHandler;

use App\Entity\Blog;
use App\Service\Billing\FailedToGetLicenseException;
use App\Service\Billing\LicenseService;
use App\Service\LinkAnalysis\Exception\LinkAnalysisCheckAlreadyPendingException;
use App\Service\LinkAnalysis\LinkAnalysisService;
use App\Service\LinkAnalysis\Message\DispatchAllLinkAnalysisChecksMessage;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Deployment;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DispatchAllLinkAnalysisChecksMessageHandler
{
    use ClockAwareTrait;

    private const int MIN_DAYS_BETWEEN_CHECKS = 14;

    public function __construct(
        private EntityManagerInterface $em,
        private LinkAnalysisService $linkAnalysisService,
        private LicenseService $licenseService,
        private InternalConfig $internalConfig,
    ) {}

    public function __invoke(DispatchAllLinkAnalysisChecksMessage $message): void
    {
        foreach ($this->getBlogs() as $blog) {
            if (!$this->isDueForCheck($blog)) {
                continue;
            }

            if (!$blog->getMeta()->link_analysis_enabled) {
                continue;
            }

            if (!$this->hasLinkAnalysisLicense($blog)) {
                continue;
            }

            try {
                $this->linkAnalysisService->createCheck($blog);
            } catch (LinkAnalysisCheckAlreadyPendingException) {
                continue;
            }

            // throttle to avoid dispatching a burst of checks at once
            usleep(100000);
        }
    }

    /**
     * @return Blog[]
     */
    private function getBlogs(): array
    {
        /** @var Blog[] */
        return $this->em->createQueryBuilder()
            ->select('b')
            ->from(Blog::class, 'b')
            ->where('b.deleted_at IS NULL')
            ->getQuery()
            ->getResult();
    }

    private function isDueForCheck(Blog $blog): bool
    {
        $lastCheck = $this->linkAnalysisService->getLastCheck($blog);

        if ($lastCheck === null) {
            return true;
        }

        $daysSinceLastCheck = $this->now()->diff($lastCheck->getCreatedAt())->days;

        return $daysSinceLastCheck >= self::MIN_DAYS_BETWEEN_CHECKS;
    }

    private function hasLinkAnalysisLicense(Blog $blog): bool
    {
        if ($this->internalConfig->getDeployment() === Deployment::ON_PREM) {
            return true;
        }

        try {
            $license = $this->licenseService->getCachedLicenseForBlog($blog);
        } catch (FailedToGetLicenseException) {
            return false;
        }

        return $license->license instanceof BlogsLicense && $license->license->linkAnalysis;
    }
}
