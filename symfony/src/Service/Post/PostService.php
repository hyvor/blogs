<?php

namespace App\Service\Post;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\PostVariant;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as OrmQB;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PostService
{
    public function __construct(
        private Connection $connection,
        private EntityManagerInterface $em,
    ) {}

    public function getPostById(int $id): ?Post
    {
        return $this->em->getRepository(Post::class)->find($id);
    }

    public function getPostByBlogAndId(Blog $blog, int $id): ?Post
    {
        return $this->em->getRepository(Post::class)->findOneBy([
            'id' => $id,
            'blog' => $blog,
        ]);
    }

    public function getPostBySlugAndLanguage(Language $language, string $slug): ?Post
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Post::class, 'p')
            ->join(PostVariant::class, 'pv', 'WITH', 'pv.post = p AND pv.language = :language AND pv.status = :status AND pv.slug = :slug')
            ->setParameter('language', $language)
            ->setParameter('status', PostVariantStatus::PUBLISHED)
            ->setParameter('slug', $slug)
            ->setMaxResults(1);

        /** @var Post|null $result */
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    public function getPostVariantByPostAndLanguage(Post $post, Language $language): ?PostVariant
    {
        return $this->em->getRepository(PostVariant::class)->findOneBy([
            'post' => $post,
            'language' => $language,
        ]);
    }

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

        $orderBys = $featuredFirst
            ? [['p.is_featured', 'DESC'], ['p.published_at', 'DESC']]
            : [['p.published_at', 'DESC']];

        return $this->getPostsForDataApi($blog, $language, $filter, $limit, $offset, $orderBys, false);
    }

    /**
     * @param array<array{0: string, 1: string}> $orderBys
     * @return array{posts: Post[], total: int}
     */
    public function getPostsForDataApi(
        Blog $blog,
        Language $language,
        ?string $filter,
        int $limit,
        int $offset,
        array $orderBys,
        bool $isPages,
    ): array {
        $qb = $this->buildPostQueryBase($blog, $language, $isPages);
        $this->applyFilter($qb, $filter);
        return $this->executePostQuery($qb, $orderBys, $limit, $offset);
    }

    /**
     * @return array{posts: Post[], total: int}
     */
    public function searchPosts(
        Blog $blog,
        Language $language,
        string $search,
        int $limit,
        int $offset,
    ): array {
        $words = preg_split('/\s+/', trim($search));
        if (empty($words) || $words === [false]) {
            return ['posts' => [], 'total' => 0];
        }

        $queryTerms = implode(' & ', array_map(fn(string $w) => $w . ':*', array_filter($words)));

        $sql = <<<SQL
SELECT p.id,
  ts_rank(
    to_tsvector(COALESCE(pv.ts_language,'simple'), COALESCE(pv.title,'') || ' ' || COALESCE(pv.slug,'') || ' ' || COALESCE(pv.description,'') || ' ' || COALESCE(pv.content_text,'')),
    to_tsquery(COALESCE(pv.ts_language,'simple'), :query)
  ) as rank
FROM posts p
JOIN post_variants pv ON pv.post_id = p.id AND pv.language_id = :lang
WHERE p.blog_id = :blog AND pv.status = 'published' AND p.is_page = false
AND to_tsvector(COALESCE(pv.ts_language,'simple'), COALESCE(pv.title,'') || ' ' || COALESCE(pv.slug,'') || ' ' || COALESCE(pv.description,'') || ' ' || COALESCE(pv.content_text,''))
    @@ to_tsquery(COALESCE(pv.ts_language,'simple'), :query)
ORDER BY rank DESC
SQL;

        $countSql = <<<SQL
SELECT COUNT(*) FROM posts p
JOIN post_variants pv ON pv.post_id = p.id AND pv.language_id = :lang
WHERE p.blog_id = :blog AND pv.status = 'published' AND p.is_page = false
AND to_tsvector(COALESCE(pv.ts_language,'simple'), COALESCE(pv.title,'') || ' ' || COALESCE(pv.slug,'') || ' ' || COALESCE(pv.description,'') || ' ' || COALESCE(pv.content_text,''))
    @@ to_tsquery(COALESCE(pv.ts_language,'simple'), :query)
SQL;

        $params = [
            'blog' => $blog->getId(),
            'lang' => $language->getId(),
            'query' => $queryTerms,
        ];

        $totalFetch = $this->connection->fetchOne($countSql, $params);
        $total = is_numeric($totalFetch) ? (int)$totalFetch : 0;

        $rows = $this->connection->fetchAllAssociative($sql . ' LIMIT :limit OFFSET :offset', array_merge($params, [
            'limit' => $limit,
            'offset' => $offset,
        ]));

        /** @var array<int|string> $ids */
        $ids = array_column($rows, 'id');

        if (empty($ids)) {
            return ['posts' => [], 'total' => $total];
        }

        $posts = $this->em->getRepository(Post::class)->findBy(['id' => $ids]);

        /** @var array<int|string, int> $idOrder */
        $idOrder = array_flip($ids);
        usort($posts, fn($a, $b) => ($idOrder[$a->getId()] ?? 0) <=> ($idOrder[$b->getId()] ?? 0));

        return ['posts' => $posts, 'total' => $total];
    }

    private function buildPostQueryBase(Blog $blog, Language $language, bool $isPage): OrmQB
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Post::class, 'p')
            ->join(PostVariant::class, 'pv', 'WITH', 'pv.post = p AND pv.language = :language AND pv.status = :status')
            ->where('p.blog = :blog')
            ->andWhere('p.is_page = :isPage')
            ->setParameter('blog', $blog)
            ->setParameter('language', $language)
            ->setParameter('status', PostVariantStatus::PUBLISHED)
            ->setParameter('isPage', $isPage);

        return $qb;
    }

    private function applyFilter(OrmQB $qb, ?string $filter): void
    {
        if ($filter === null || $filter === '') {
            return;
        }

        try {
            FilterQ::expression($filter)
                ->queryBuilder($qb)
                ->keys(function ($keys) {
                    $keys->add('id', 'p.id')->valueType('int');
                    $keys->add('published_at', 'p.published_at')->valueType('date');
                    $keys->add('created_at', 'p.created_at')->valueType('date');
                    $keys->add('updated_at', 'pv.updated_at')->valueType('date');
                    $keys->add('is_featured', 'p.is_featured')->valueType('bool');
                    $keys->add('slug', 'pv.slug')->valueType('string');
                    $keys->add('title', 'pv.title')->valueType('string');
                    $keys->add('words', 'pv.words')->valueType('int');
                    $keys->add('featured_image_url', 'p.featured_image_url')->valueType(['string', 'null']);
                    $keys->add('canonical_url', 'p.canonical_url')->valueType(['string', 'null']);

                    $tagJoined = false;
                    $tagJoinFn = function (OrmQB $qb) use (&$tagJoined) {
                        if (!$tagJoined) {
                            $qb->join('p.tags', 'tag_filter');
                            $tagJoined = true;
                        }
                    };
                    $keys->add('tag.id', 'tag_filter.id')->valueType('int')->join($tagJoinFn);
                    $keys->add('tag.slug', 'tag_filter.slug')->valueType('string')->join($tagJoinFn);

                    $authorJoined = false;
                    $authorJoinFn = function (OrmQB $qb) use (&$authorJoined) {
                        if (!$authorJoined) {
                            $qb->join('p.authors', 'author_filter');
                            $authorJoined = true;
                        }
                    };
                    $keys->add('author.id', 'author_filter.id')->valueType('int')->join($authorJoinFn);
                    $keys->add('author.slug', 'author_filter.slug')->valueType('string')->join($authorJoinFn);
                })
                ->addWhere();
        } catch (FilterQException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
    }

    /**
     * @param array<array{0: string, 1: string}> $orderBys
     * @return array{posts: Post[], total: int}
     */
    private function executePostQuery(OrmQB $qb, array $orderBys, int $limit, int $offset): array
    {
        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT p.id)');
        $totalFetch = $countQb->getQuery()->getSingleScalarResult();
        $total = is_numeric($totalFetch) ? (int)$totalFetch : 0;

        if ($total === 0) {
            return ['posts' => [], 'total' => 0];
        }

        $idQb = clone $qb;
        $idQb->select('p.id as pid, pv.title, pv.words, pv.updated_at, p.is_featured, p.published_at, p.created_at')
             ->groupBy('p.id, pv.title, pv.words, pv.updated_at, p.is_featured, p.published_at, p.created_at');

        foreach ($orderBys as [$column, $direction]) {
            $idQb->addOrderBy($column, $direction);
        }

        $idQb->setMaxResults($limit)
             ->setFirstResult($offset);

        /** @var array<array{pid: int}> $rows */
        $rows = $idQb->getQuery()->getArrayResult();
        $ids = array_column($rows, 'pid');

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
