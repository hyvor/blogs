<?php

namespace App\Api\Sudo\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SudoAnalyticsService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getBlogTotal(): int
    {
        return (int) $this->entityManager->getConnection()
            ->executeQuery('SELECT COUNT(*) FROM blogs')
            ->fetchOne();
    }

    public function getBlog30DaysChange(): int
    {
        $date = $this->now()->modify('-30 days')->format('Y-m-d H:i:s');

        return (int) $this->entityManager->getConnection()
            ->executeQuery(
                'SELECT COUNT(*) FROM blogs WHERE created_at > :date',
                ['date' => $date]
            )
            ->fetchOne();
    }

    public function getBlogsWithCustomDomains(): int
    {
        return (int) $this->entityManager->getConnection()
            ->executeQuery('SELECT COUNT(*) FROM blogs WHERE hosting_domain IS NOT NULL')
            ->fetchOne();
    }

    /**
     * @return array<string, int>
     */
    public function getBlogByMonth(): array
    {
        $rows = $this->entityManager->getConnection()
            ->executeQuery(
                "SELECT COUNT(*) as count, TO_CHAR(created_at, 'YYYY-MM') AS month
                 FROM blogs
                 GROUP BY month
                 ORDER BY month"
            )
            ->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['month']] = (int) $row['count'];
        }

        return $result;
    }
}
