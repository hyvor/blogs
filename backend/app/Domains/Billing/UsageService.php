<?php

namespace App\Domains\Billing;

use App\Domains\Billing\Usage\AiTokensUsage;
use App\Domains\Billing\Usage\AutoTranslateCharsUsage;
use App\Domains\Billing\Usage\StorageUsage;
use App\Domains\Billing\Usage\UsersUsage;
use App\Models\Blog;
use Illuminate\Database\Connection;

class UsageService
{

    public function __construct(
        private Connection $db
    ) {
    }

    public function getUsersUsage(int $organizationId): int
    {
        $result = $this->db->selectOne(<<<SQL
            SELECT SUM(
                COALESCE((counts->>'users')::INT, 0)
            ) AS count
            FROM blogs
            WHERE organization_id = ? 
        SQL, [$organizationId]);

        return $result->count ?? 0;
    }

    public function getStorageUsageBytes(int $organizationId): int
    {
        $result = $this->db->selectOne(<<<SQL
            SELECT SUM(
                COALESCE((counts->>'media')::INT, 0)
            ) AS count
            FROM blogs
            WHERE organization_id = ?
        SQL, [$organizationId]);

        return $result->count ?? 0;
    }

    public function getAutoTranslateCharsUsageThisMonth(int $organizationId): int
    {
        $result = $this->db->selectOne(<<<SQL
            SELECT SUM(chars) AS count
            FROM auto_translations
            INNER JOIN blogs ON auto_translations.blog_id = blogs.id
            WHERE blogs.organization_id = ?
            AND auto_translations.created_at >= ?
        SQL, [$organizationId, now()->startOfMonth()]);

        return $result->count ?? 0;
    }

    public function getAiTokensUsage(int $organizationId): int
    {
        $result = $this->db->selectOne(<<<SQL
            SELECT SUM(tokens_total) AS count
            FROM gpt_prompts
            INNER JOIN blogs ON gpt_prompts.blog_id = blogs.id
            WHERE blogs.organization_id = ?
            AND gpt_prompts.created_at >= ?
        SQL, [$organizationId, now()->startOfMonth()]);

        return $result->count ?? 0;
    }

    public function usersLimitReached(Blog $blog): bool
    {
        return $this->reached($blog, 'users', [$this, 'getUsersUsage']);
    }

    public function storageLimitReached(Blog $blog): bool
    {
        return $this->reached($blog, 'storage', [$this, 'getStorageUsageBytes']);
    }

    public function autoTranslationCharsLimitReached(Blog $blog): bool
    {
        return $this->reached($blog, 'autoTranslationsChars', [$this, 'getAutoTranslateCharsUsageThisMonth']);
    }

    public function aiTokensLimitReached(Blog $blog): bool
    {
        return $this->reached($blog, 'aiTokens', [$this, 'getAiTokensUsage']);
    }

    /**
     * @param array{0: self, 1: string} $usageFunc
     */
    private function reached(Blog $blog, string $licenseKey, array $usageFunc): bool
    {
        $license = LicenseService::getLicense($blog);

        if (!$license) {
            return true;
        }

        if ($blog->organization_id === null) {
            // this should be a temp, dev, or preview blog
            return false;
        }

        $limit = $license->$licenseKey ?? 0;

        assert(is_callable($usageFunc));
        $usage = $usageFunc();

        return $usage >= $limit;
    }


}
