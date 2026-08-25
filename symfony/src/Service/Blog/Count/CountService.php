<?php

namespace App\Service\Blog\Count;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Language;
use App\Entity\Media;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\User;
use App\Service\Language\LanguageService;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\LockFactory;

class CountService
{
    /**
     * Recalculation is a handful of count queries; this is just a safety net
     * in case a worker dies mid-recalculation without releasing the lock.
     */
    private const int LOCK_TTL = 120;

    public function __construct(
        private EntityManagerInterface $em,
        private LanguageService $languageService,
        private LockFactory $lockFactory,
    ) {}

    /**
     * Recalculation is idempotent
     * Uses a lock to avoid multiple recalculations in parallel
     */
    public function recalculate(
        Blog $blog,
        CountType $type,
        ?array $entityIds = null
    ): void
    {
        if ($entityIds === null) {
            // when entity IDs are set, it's relatively cheap to recalculate
            // also, messages with entity IDs are dispatched from specific events that do not tend to overlap
            // so, lock is only used when entity IDs are not added

            $lock = $this->lockFactory->createLock(
                sprintf('count_recalculate_%d_%s', $blog->getId(), $type->value),
                self::LOCK_TTL,
                autoRelease: false,
            );

            if (!$lock->acquire()) {
                return;
            }
        }

        try {
            match ($type) {
                CountType::POSTS_OF_BLOG => $this->recalculatePostCountsOnBlog($blog),
                CountType::POSTS_OF_USERS => $this->recalculatePostsCountOnUsers($blog, $entityIds),
                CountType::POSTS_OF_TAGS => $this->recalculatePostsCountOnTags($blog, $entityIds),
                CountType::USERS_OF_BLOG => $this->recalculateUsersCountOnBlog($blog),
                CountType::MEDIA_OF_BLOG => $this->recalculateMediaCountOnBlog($blog),
            };
        } finally {
            if (isset($lock)) {
                $lock->release();
            }
        }
    }

    private function recalculatePostCountsOnBlog(Blog $blog): void
    {
        $language = $this->languageService->getPrimaryLanguage($blog);

        $statusCounts = $this->countVariantsByStatus($blog, $language);

        $featured = (int) $this->em->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Post::class, 'p')
            ->where('p.blog = :blog')
            ->andWhere('p.is_featured = true')
            ->andWhere('p.is_page = false')
            ->setParameter('blog', $blog)
            ->getQuery()
            ->getSingleScalarResult();

        $this->mergeCounts($blog, [
            'posts' => $statusCounts[PostVariantStatus::PUBLISHED->value],
            'posts_draft' => $statusCounts[PostVariantStatus::DRAFT->value],
            'posts_scheduled' => $statusCounts[PostVariantStatus::SCHEDULED->value],
            'posts_featured' => $featured,
        ]);
    }

    /**
     * @return array<string, int> keyed by PostVariantStatus value, always containing every status
     */
    private function countVariantsByStatus(Blog $blog, Language $language): array
    {
        $counts = array_fill_keys(array_map(fn(PostVariantStatus $status) => $status->value, PostVariantStatus::cases()), 0);

        /** @var list<array{status: PostVariantStatus|string, count: int|string}> $rows */
        $rows = $this->em->createQueryBuilder()
            ->select('pv.status AS status', 'COUNT(pv.id) AS count')
            ->from(PostVariant::class, 'pv')
            ->join('pv.post', 'p')
            ->where('pv.language = :language')
            ->andWhere('p.blog = :blog')
            ->andWhere('p.is_page = false')
            ->groupBy('pv.status')
            ->setParameter('language', $language)
            ->setParameter('blog', $blog)
            ->getQuery()
            ->getResult();

        foreach ($rows as $row) {
            $status = $row['status'] instanceof PostVariantStatus ? $row['status']->value : $row['status'];
            $counts[$status] = (int) $row['count'];
        }

        return $counts;
    }


    /**
     * @param int[]|null $userIds
     */
    private function recalculatePostsCountOnUsers(Blog $blog, ?array $userIds = null): void
    {
        $this->recalculatePostAuthorsOrTags($blog, $userIds, 'users', 'post_author', 'user_id');
    }

    /**
     * @param int[]|null $tagIds
     */
    private function recalculatePostsCountOnTags(Blog $blog, ?array $tagIds = null): void
    {
        $this->recalculatePostAuthorsOrTags($blog, $tagIds, 'tags', 'post_tag', 'tag_id');
    }

    private function recalculatePostAuthorsOrTags(
        Blog $blog,
        ?array $entityIds,
        string $table,
        string $pivotTable,
        string $pivotColumn // 'user_id' or 'tag_id'
    ): void
    {
        $language = $this->languageService->getPrimaryLanguage($blog);

        $params = [$language->getId(), $blog->getId()];
        $types = ['integer', 'integer'];

        $inWhere = '';
        if ($entityIds !== null && count($entityIds) > 0) {
            $inWhere = ' AND t.id IN (?)';
            $params[] = $entityIds;
            $types[] = ArrayParameterType::INTEGER;
        }

        $this->em->getConnection()->executeStatement(
            <<<SQL
            UPDATE $table AS t SET posts_count = (
                SELECT COUNT($pivotTable.id)
                FROM $pivotTable
                INNER JOIN post_variants ON $pivotTable.post_id = post_variants.post_id
                INNER JOIN posts ON post_variants.post_id = posts.id
                WHERE
                    $pivotTable.$pivotColumn = t.id AND
                    post_variants.language_id = ? AND
                    post_variants.status = 'published' AND
                    posts.is_page = false
            )
            WHERE t.blog_id = ?{$inWhere}
            SQL,
            $params,
            $types
        );
    }

    private function recalculateUsersCountOnBlog(Blog $blog): void
    {
        $users = $this->em->getRepository(User::class)->count(['blog' => $blog]);
        $this->mergeCounts($blog, ['users' => $users]);
    }

    private function recalculateMediaCountOnBlog(Blog $blog): void
    {
        $size = (int) $this->em->createQueryBuilder()
            ->select('COALESCE(SUM(m.size), 0)')
            ->from(Media::class, 'm')
            ->where('m.blog = :blog')
            ->setParameter('blog', $blog)
            ->getQuery()
            ->getSingleScalarResult();

        $this->mergeCounts($blog, ['media' => $size]);
    }

    /**
     * @param array<string, int> $counts
     */
    private function mergeCounts(Blog $blog, array $counts): void
    {
        $blog->setCounts([...($blog->getCounts() ?? []), ...$counts]);
        $this->em->flush();
    }
}
