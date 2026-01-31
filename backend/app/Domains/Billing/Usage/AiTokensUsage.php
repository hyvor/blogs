<?php

namespace App\Domains\Billing\Usage;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\Usage\UsageAbstract;
use Illuminate\Database\Connection;

class AiTokensUsage extends UsageAbstract
{

    public function __construct(private Connection $db)
    {
        parent::__construct();
    }

    public function getLicenseType(): string
    {
        return BlogsLicense::class;
    }

    public function getKey(): string
    {
        return 'aiTokens';
    }

    public function usageOfOrganization(int $organizationId): int
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

}
