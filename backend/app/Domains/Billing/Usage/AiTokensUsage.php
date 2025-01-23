<?php

namespace App\Domains\Billing\Usage;

use App\Models\GptPrompt;
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

    public function usageOfUser(int $userId): int
    {
        $result = $this->db->selectOne(<<<SQL
            SELECT SUM(tokens_total) AS count
            FROM gpt_prompts
            INNER JOIN blogs ON gpt_prompts.blog_id = blogs.id
            WHERE blogs.hyvor_user_id = ?
            AND gpt_prompts.created_at >= ?
        SQL, [$userId, now()->startOfMonth()]);

        return $result->count ?? 0;
    }

    public function usageOfResource(int $resourceId): int
    {
        return intval(GptPrompt::withTrashed()
            ->where('blog_id', $resourceId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('tokens_total'));
    }
}
