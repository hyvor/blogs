<?php

namespace App\Domains\Billing\Usage;

use App\Models\AutoTranslation;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\Usage\UsageAbstract;
use Illuminate\Database\Connection;

class AutoTranslateCharsUsage extends UsageAbstract
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
        return 'autoTranslationsChars';
    }

    public function usageOfUser(int $userId): int
    {
        $result = $this->db->selectOne(<<<SQL
            SELECT SUM(chars) AS count
            FROM auto_translations
            INNER JOIN blogs ON auto_translations.blog_id = blogs.id
            WHERE blogs.hyvor_user_id = ?
            AND auto_translations.created_at >= ?
        SQL, [$userId, now()->startOfMonth()]);

        return $result->count ?? 0;
    }

    public function usageOfResource(int $resourceId): int
    {
        return intval(AutoTranslation::where('blog_id', $resourceId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('chars'));
    }
}
