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

    /**
     * Storage is an organization-level limit (shared by all blogs of the organization).
     *
     * @param int $additionalBytes size of the file about to be stored (if known)
     */
    public function storageLimitReached(Blog $blog, int $additionalBytes = 0): bool
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

        return $usage + $additionalBytes > $limit || $usage >= $limit;
    }

    /**
     * Users is a per-blog limit (BlogsLicense::$users = users per blog).
     */
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

        if ($limit < 0) {
            return false;
        }

        return $this->getUsersUsage($blog) >= $limit;
    }

    /**
     * Languages is a per-blog limit (BlogsLicense::$languages = languages per blog, -1 = unlimited).
     */
    public function languagesLimitReached(Blog $blog): bool
    {
        $organizationId = $blog->getOrganizationId();

        if ($organizationId === null) {
            return false;
        }

        try {
            $license = $this->billing->license($organizationId);
        } catch (CommsApiFailedException) {
            return true;
        }

        $bl = $license->license instanceof BlogsLicense ? $license->license : null;
        $limit = $bl->languages ?? 0;

        if ($limit < 0) {
            return false;
        }

        return $this->getLanguagesUsage($blog) >= $limit;
    }

    public function getUsersUsage(Blog $blog): int
    {
        /** @var ?int $result */
        $result = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM users WHERE blog_id = ?',
            [$blog->getId()],
        );

        return (int)$result;
    }

    public function getLanguagesUsage(Blog $blog): int
    {
        /** @var ?int $result */
        $result = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM languages WHERE blog_id = ?',
            [$blog->getId()],
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

    /**
     * Percentage (0-100) of the organization's monthly AI cost allowance used so far.
     */
    public function getAiTokensUsage(int $organizationId, ?BlogsLicense $license): float
    {
        $limitCents = $license->aiCost ?? 0;

        if ($limitCents <= 0) {
            return 0.0;
        }

        $startOfMonth = $this->now()->modify('first day of this month midnight')->format('Y-m-d H:i:s');

        /** @var ?float $result */
        $result = $this->connection->fetchOne(
            "SELECT SUM(COALESCE(ai_messages.total_tokens_usd_cost, 0)) AS cost
             FROM ai_messages
             INNER JOIN ai_conversations ON ai_messages.conversation_id = ai_conversations.id
             INNER JOIN blogs ON ai_conversations.blog_id = blogs.id
             WHERE blogs.organization_id = ?
             AND ai_messages.created_at >= ?",
            [$organizationId, $startOfMonth],
        );

        $usedCents = ((float) $result) * 100;

        return min(($usedCents / $limitCents) * 100, 100);
    }

    public function getBlogsUsage(int $organizationId): int
    {
        /** @var ?int $result */
        $result = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM blogs WHERE organization_id = ? AND type = 'default' AND deleted_at IS NULL",
            [$organizationId],
        );

        return (int)$result;
    }

}
