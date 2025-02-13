<?php

namespace App\Domains\Billing\Usage;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\Usage\UsageAbstract;
use Illuminate\Database\Connection;

/**
 * @extends UsageAbstract<BlogsLicense>
 */
class UsersUsage extends UsageAbstract
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
        return 'users';
    }

    public function usageOfUser(int $userId): int
    {
        $result = $this->db->selectOne(<<<SQL
            SELECT SUM(
                COALESCE((counts->>'users')::INT, 0)
            ) AS count
            FROM blogs
            WHERE hyvor_user_id = ? 
        SQL, [$userId]);

        return $result->count ?? 0;
    }

    public function usageOfResource(int $resourceId): int
    {
        $result = $this->db->selectOne(<<<SQL
            SELECT COALESCE((counts->>'users')::INT, 0) AS count
            FROM blogs
            WHERE id = ?
        SQL, [$resourceId]);

        return $result->count ?? 0;
    }
}
