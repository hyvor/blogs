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
    public function recalculate(Blog $blog, CountType $type): void
    {
        $lock = $this->lockFactory->createLock(
            sprintf('count_recalculate_%d_%s', $blog->getId(), $type->value),
            self::LOCK_TTL,
            autoRelease: false,
        );

        if (!$lock->acquire()) {
            return;
        }

        try {
            match ($type) {
                CountType::POSTS => $this->recalculatePostCounts($blog),
                CountType::AUTHORS => $this->recalculateAuthorCounts($blog),
                CountType::TAGS => $this->recalculateTagCounts($blog),
                CountType::USERS => $this->recalculateUserCounts($blog),
                CountType::MEDIA => $this->recalculateMediaCounts($blog),
            };
        } finally {
            $lock->release();
        }
    }

    private function recalculatePostCounts(Blog $blog): void
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

    // this might not the most performant for large blogs
    // we may want to optimize this to only recalculate the counts for the affected users later
    // same for tags below
    private function recalculateAuthorCounts(Blog $blog): void
    {
        $language = $this->languageService->getPrimaryLanguage($blog);

        $this->em->getConnection()->executeStatement(
            <<<'SQL'
            UPDATE users AS u SET posts_count = (
                SELECT COUNT(post_author.id)
                FROM post_author
                INNER JOIN post_variants ON post_author.post_id = post_variants.post_id
                INNER JOIN posts ON post_variants.post_id = posts.id
                WHERE
                    post_author.user_id = u.id AND
                    post_variants.language_id = ? AND
                    post_variants.status = 'published' AND
                    posts.is_page = false
            )
            WHERE u.blog_id = ?
            SQL,
            [$language->getId(), $blog->getId()],
        );
    }

    private function recalculateTagCounts(Blog $blog): void
    {
        $language = $this->languageService->getPrimaryLanguage($blog);

        $this->em->getConnection()->executeStatement(
            <<<'SQL'
            UPDATE tags AS t SET posts_count = (
                SELECT COUNT(post_tag.id)
                FROM post_tag
                INNER JOIN post_variants ON post_tag.post_id = post_variants.post_id
                INNER JOIN posts ON post_variants.post_id = posts.id
                WHERE
                    post_tag.tag_id = t.id AND
                    post_variants.language_id = ? AND
                    post_variants.status = 'published' AND
                    posts.is_page = false
            )
            WHERE t.blog_id = ?
            SQL,
            [$language->getId(), $blog->getId()],
        );
    }

    private function recalculateUserCounts(Blog $blog): void
    {
        $users = $this->em->getRepository(User::class)->count(['blog' => $blog]);
        $this->mergeCounts($blog, ['users' => $users]);
    }

    private function recalculateMediaCounts(Blog $blog): void
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
