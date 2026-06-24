<?php

namespace App\Service\Billing;

use App\Entity\Blog;
use Doctrine\DBAL\Connection;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Symfony\Component\Clock\ClockAwareTrait;

class UsageService
{
    use ClockAwareTrait;

    public function __construct(
        private Connection $connection,
        private BillingInterface $billing,
    ) {}

    public function storageLimitReached(Blog $blog): bool
    {
        $organizationId = $blog->getOrganizationId();

        if ($organizationId === null) {
            // temp, dev, or preview blogs have no organization, and no limits
            return false;
        }

        try {
            $license = $this->billing->license($organizationId);
        } catch (CommsApiFailedException) {
            return true;
        }

        $bl = $license->license instanceof BlogsLicense ? $license->license : null;
        $limit = $bl->storage ?? 0;
        $usage = $this->getStorageUsageBytes($organizationId);

        return $usage >= $limit;
    }

    public function usersLimitReached(Blog $blog): bool
    {
        $organizationId = $blog->getOrganizationId();

        if ($organizationId === null) {
            // temp, dev, or preview blogs have no organization, and no limits
            return false;
        }

        try {
            $license = $this->billing->license($organizationId);
        } catch (CommsApiFailedException) {
            return true;
        }

        $bl = $license->license instanceof BlogsLicense ? $license->license : null;
        $limit = $bl->users ?? 0;
        $usage = $this->getUsersUsage($organizationId);

        return $usage >= $limit;
    }

    public function getUsersUsage(int $organizationId): int
    {
        /** @var ?int $result */
        $result = $this->connection->fetchOne(
            "SELECT SUM(COALESCE((counts->>'users')::INT, 0)) AS count
             FROM blogs
             WHERE organization_id = ?",
            [$organizationId],
        );

        return (int)$result;
    }

    public function getStorageUsageBytes(int $organizationId): int
    {
        /** @var ?int $result */
        $result = $this->connection->fetchOne(
            "SELECT SUM(COALESCE((counts->>'media')::INT, 0)) AS count
             FROM blogs
             WHERE organization_id = ?",
            [$organizationId],
        );

        return (int)$result;
    }

    public function getAutoTranslateCharsUsageThisMonth(int $organizationId): int
    {
        $startOfMonth = $this->now()->modify('first day of this month midnight')->format('Y-m-d H:i:s');

        /** @var ?int $result */
        $result = $this->connection->fetchOne(
            "SELECT SUM(chars) AS count
             FROM auto_translations
             INNER JOIN blogs ON auto_translations.blog_id = blogs.id
             WHERE blogs.organization_id = ?
             AND auto_translations.created_at >= ?",
            [$organizationId, $startOfMonth],
        );

        return (int)$result;
    }

    public function getAiTokensUsage(int $organizationId): int
    {
        $startOfMonth = $this->now()->modify('first day of this month midnight')->format('Y-m-d H:i:s');

        /** @var ?int $result */
        $result = $this->connection->fetchOne(
            "SELECT SUM(tokens_total) AS count
             FROM gpt_prompts
             INNER JOIN blogs ON gpt_prompts.blog_id = blogs.id
             WHERE blogs.organization_id = ?
             AND gpt_prompts.created_at >= ?",
            [$organizationId, $startOfMonth],
        );

        return (int)$result;
    }

    public function getBlogsUsage(int $organizationId): int
    {
        /** @var ?int $result */
        $result = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM blogs WHERE organization_id = ? AND type = 'default'",
            [$organizationId],
        );

        return (int)$result;
    }

}
