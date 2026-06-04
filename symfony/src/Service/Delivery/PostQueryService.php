<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Post;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class PostQueryService
{
    public function __construct(
        private Connection $connection,
        private EntityManagerInterface $em,
    ) {}

    /**
     * @return array{posts: Post[], total: int}
     */
    public function getPostsWithFilter(
        Blog $blog,
        Language $language,
        ?string $filter,
        int $limit = 10,
        int $offset = 0,
        bool $featuredFirst = true,
    ): array {
        if ($filter === null) {
            return ['posts' => [], 'total' => 0];
        }

        $qb = $this->connection->createQueryBuilder();

        // Use GROUP BY on p.id, p.is_featured, p.published_at to allow ORDER BY in PostgreSQL
        $qb->select('p.id', 'p.is_featured', 'p.published_at')
            ->from('posts', 'p')
            ->join('p', 'post_variants', 'pv', 'pv.post_id = p.id AND pv.language_id = :language_id')
            ->where('p.blog_id = :blog_id')
            ->andWhere("pv.status = 'published'")
            ->andWhere('p.is_page = false')
            ->groupBy('p.id, p.is_featured, p.published_at')
            ->setParameter('blog_id', $blog->getId())
            ->setParameter('language_id', $language->getId());

        $this->applyFilter($qb, $filter);

        if ($featuredFirst) {
            $qb->orderBy('p.is_featured', 'DESC')->addOrderBy('p.published_at', 'DESC');
        } else {
            $qb->orderBy('p.published_at', 'DESC');
        }

        $totalQb = clone $qb;
        $totalFetch = $totalQb->select('COUNT(DISTINCT p.id)')->resetOrderBy()->resetGroupBy()->executeQuery()->fetchOne();
        $total = is_numeric($totalFetch) ? (int)$totalFetch : 0;

        $qb->setMaxResults($limit)->setFirstResult($offset);
        $rows = $qb->executeQuery()->fetchAllAssociative();
        /** @var array<int|string> $ids */
        $ids = array_column($rows, 'id');

        if (empty($ids)) {
            return ['posts' => [], 'total' => $total];
        }

        $posts = $this->em->getRepository(Post::class)->findBy(['id' => $ids]);

        // Re-sort to match order from query
        /** @var array<int|string, int> $idOrder */
        $idOrder = array_flip($ids);
        usort($posts, fn($a, $b) => ($idOrder[$a->getId()] ?? 0) <=> ($idOrder[$b->getId()] ?? 0));

        return ['posts' => $posts, 'total' => $total];
    }

    private function applyFilter(QueryBuilder $qb, string $filter): void
    {
        if ($filter === '') {
            return;
        }

        // tag.slug='value'
        if (preg_match("/^tag\.slug='([^']+)'$/", $filter, $m)) {
            $qb->join('p', 'post_tag', 'pt', 'pt.post_id = p.id')
               ->join('pt', 'tags', 't_filter', 't_filter.id = pt.tag_id')
               ->andWhere('t_filter.slug = :filter_tag_slug')
               ->setParameter('filter_tag_slug', $m[1]);
            return;
        }

        // author.slug='value'
        if (preg_match("/^author\.slug='([^']+)'$/", $filter, $m)) {
            $qb->join('p', 'post_author', 'pa_filter', 'pa_filter.post_id = p.id')
               ->join('pa_filter', 'users', 'u_filter', 'u_filter.id = pa_filter.user_id')
               ->andWhere('u_filter.slug = :filter_author_slug')
               ->setParameter('filter_author_slug', $m[1]);
            return;
        }

        // is_featured=true
        if ($filter === 'is_featured=true') {
            $qb->andWhere('p.is_featured = true');
            return;
        }
    }
}
