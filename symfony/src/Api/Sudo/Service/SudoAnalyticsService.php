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
        /** @var numeric-string|int|false $count */
        $count = $this->entityManager->getConnection()
            ->executeQuery('SELECT COUNT(*) FROM blogs')
            ->fetchOne();

        return (int) $count;
    }

    public function getBlog30DaysChange(): int
    {
        $date = $this->now()->modify('-30 days')->format('Y-m-d H:i:s');

        /** @var numeric-string|int|false $count */
        $count = $this->entityManager->getConnection()
            ->executeQuery(
                'SELECT COUNT(*) FROM blogs WHERE created_at > :date',
                ['date' => $date]
            )
            ->fetchOne();

        return (int) $count;
    }

    public function getBlogsWithCustomDomains(): int
    {
        /** @var numeric-string|int|false $count */
        $count = $this->entityManager->getConnection()
            ->executeQuery('SELECT COUNT(*) FROM blogs WHERE hosting_domain IS NOT NULL')
            ->fetchOne();

        return (int) $count;
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
            /** @var array{month: string, count: numeric-string|int} $row */
            $result[$row['month']] = (int) $row['count'];
        }

        return $result;
    }
}
