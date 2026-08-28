<?php

namespace App\Service\Post;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Post;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class PostSearchService
{

    public function __construct(
        private Connection $connection,
        private EntityManagerInterface $em,
        private FullTextSearchService $fullTextSearchService
    ) {}

    /**
     * @return array{posts: Post[], total: int}
     */
    public function search(
        Blog $blog,
        Language $language,
        string $search,
        int $limit,
        int $offset,
        bool $isPage = false,
        ?bool $isPublished = true,
    ): array {
        $searchQuery = $this->fullTextSearchService->getSearchQuery($search);
        $isPageSql = $isPage ? 'true' : 'false';

        $where = "pv.calculated_ts @@ to_tsquery(pv.ts_language, :query)
            AND pv.language_id = :language
            AND p.blog_id = :blog
            AND p.is_page = $isPageSql";

        if ($isPublished === true) {
            $where .= " AND pv.status = 'published'";
        }

        $params = [
            'query' => $searchQuery,
            'language' => $language->getId(),
            'blog' => $blog->getId(),
        ];

        $totalFetch = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM post_variants pv JOIN posts p ON p.id = pv.post_id WHERE $where",
            $params
        );
        $total = is_numeric($totalFetch) ? (int)$totalFetch : 0;

        if ($total === 0) {
            return ['posts' => [], 'total' => 0];
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT pv.post_id FROM post_variants pv JOIN posts p ON p.id = pv.post_id WHERE $where
            ORDER BY ts_rank(pv.calculated_ts, to_tsquery(pv.ts_language, :query)) DESC
            LIMIT :limit OFFSET :offset",
            array_merge($params, [
                'limit' => $limit,
                'offset' => $offset,
            ])
        );

        /** @var array<int|string> $ids */
        $ids = array_column($rows, 'post_id');

        if (empty($ids)) {
            return ['posts' => [], 'total' => $total];
        }

        $posts = $this->em->getRepository(Post::class)->findBy(['id' => $ids]);

        /** @var array<int|string, int> $idOrder */
        $idOrder = array_flip($ids);
        usort($posts, fn($a, $b) => ($idOrder[$a->getId()] ?? 0) <=> ($idOrder[$b->getId()] ?? 0));

        return ['posts' => $posts, 'total' => $total];
    }
}
