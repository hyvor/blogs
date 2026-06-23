<?php

namespace App\Service\Post;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\RedirectType;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\Language\LanguageService;
use App\Service\Redirect\RedirectService;
use App\Service\Route\PermalinkService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as OrmQB;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PostService
{
    use ClockAwareTrait;

    private const SLUG_INVALID_CHARACTERS = [
        ':',
        '/',
        '?',
        '#',
        '[',
        ']',
        '@',
        '!',
        '$',
        '&',
        "'",
        '(',
        ')',
        '*',
        '+',
        ',',
        ';',
        '=',
        '%',
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private Connection $connection,
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private RedirectService $redirectService,
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

    // Console API methods

    /**
     * @return array{posts: Post[], total: int}
     */
    public function getConsolePosts(
        Blog $blog,
        Language $language,
        ?string $status = null,
        ?int $authorId = null,
        ?int $tagId = null,
        ?int $startTimestamp = null,
        ?int $endTimestamp = null,
        ?string $search = null,
        int $limit = 50,
        int $offset = 0,
    ): array {
        $where = 'p.blog_id = :blog AND p.is_page = false AND pv.language_id = :language';
        $params = ['blog' => $blog->getId(), 'language' => $language->getId()];
        $joins = '';

        if ($authorId !== null) {
            $joins .= ' JOIN post_author pa ON pa.post_id = p.id AND pa.user_id = :author_id';
            $params['author_id'] = $authorId;
        }

        if ($tagId !== null) {
            $joins .= ' JOIN post_tag pt ON pt.post_id = p.id AND pt.tag_id = :tag_id';
            $params['tag_id'] = $tagId;
        }

        if ($status !== null) {
            if ($status === 'featured') {
                $where .= ' AND p.is_featured = true';
            } else {
                $where .= ' AND pv.status = :status';
                $params['status'] = $status;
            }
        }

        if ($startTimestamp !== null && $endTimestamp !== null) {
            $where .= ' AND COALESCE(p.published_at, p.created_at) > :start AND COALESCE(p.published_at, p.created_at) < :end';
            $params['start'] = date('Y-m-d H:i:s', $startTimestamp);
            $params['end'] = date('Y-m-d H:i:s', $endTimestamp);
        }

        if ($search !== null && $search !== '') {
            $searchQuery = $this->getSearchQuery($search);
            $where .= " AND pv.calculated_ts @@ to_tsquery(pv.ts_language, :search)";
            $params['search'] = $searchQuery;
            $select = "p.id, ts_rank(pv.calculated_ts, to_tsquery(pv.ts_language, :search)) AS rank";
            $orderBy = "rank DESC";
        } else {
            $select = "p.id, CASE pv.status WHEN 'draft' THEN 1 ELSE 2 END AS sort_status, p.published_at, p.created_at";
            $orderBy = "sort_status ASC, p.published_at DESC NULLS LAST, p.created_at DESC";
        }

        $totalFetch = $this->connection->fetchOne(
            "SELECT COUNT(DISTINCT p.id) FROM posts p JOIN post_variants pv ON pv.post_id = p.id $joins WHERE $where",
            $params
        );
        $total = is_numeric($totalFetch) ? (int)$totalFetch : 0;

        if ($total === 0) {
            return ['posts' => [], 'total' => 0];
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT DISTINCT $select FROM posts p JOIN post_variants pv ON pv.post_id = p.id $joins WHERE $where ORDER BY $orderBy LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $limit, 'offset' => $offset])
        );

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

    private function getSearchQuery(string $search): string
    {
        $replaced = (string)preg_replace('/[*:|&!()]/', '', $search);
        $replaced = (string)preg_replace('/\s+/', ':* | ', $replaced);
        return $replaced . ':*';
    }

    /**
     * @return Post[]
     */
    public function getPages(Blog $blog): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Post::class, 'p')
            ->where('p.blog = :blog')
            ->andWhere('p.is_page = true')
            ->setParameter('blog', $blog)
            ->orderBy('p.id', 'DESC');

        /** @var Post[] */
        return $qb->getQuery()->getResult();
    }

    /**
     * @param User[] $authors
     */
    public function createPost(
        Blog $blog,
        array $authors = [],
        bool $isPage = false,
        bool $isFeatured = false,
        ?string $featuredImageUrl = null,
        ?string $canonicalUrl = null,
        ?string $codeHead = null,
        ?string $codeFoot = null,
    ): Post {
        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);

        $post = $this->instantiatePost(
            $blog,
            $isPage,
            $isFeatured,
            $featuredImageUrl,
            $canonicalUrl,
            $codeHead,
            $codeFoot,
        );

        $variant = $this->createPostVariant($post, $primaryLanguage, flush: false);
        $post->getVariants()->add($variant);

        $this->setPostAuthors($post, $authors, flush: false);

        $this->em->flush();

        return $post;
    }

    private function instantiatePost(
        Blog $blog,
        bool $isPage,
        bool $isFeatured = false,
        ?string $featuredImageUrl = null,
        ?string $canonicalUrl = null,
        ?string $codeHead = null,
        ?string $codeFoot = null,
    ): Post {
        $post = new Post();
        $post->setBlog($blog);
        $post->setIsPage($isPage);
        $post->setIsFeatured($isFeatured);
        $post->setFeaturedImageUrl($featuredImageUrl);
        $post->setCanonicalUrl($canonicalUrl);
        $post->setCodeHead($codeHead);
        $post->setCodeFoot($codeFoot);
        $post->setCreatedAt($this->now());
        $post->setUpdatedAt($this->now());
        $this->em->persist($post);

        return $post;
    }

    /**
     * @param array{
     *     is_featured?: bool,
     *     featured_image_url?: ?string,
     *     canonical_url?: ?string,
     *     code_head?: ?string,
     *     code_foot?: ?string,
     *     published_at?: \DateTimeImmutable|null,
     * } $data
     */
    public function updatePost(Post $post, array $data): Post
    {
        if (array_key_exists('is_featured', $data)) {
            $post->setIsFeatured($data['is_featured']);
        }
        if (array_key_exists('featured_image_url', $data)) {
            $post->setFeaturedImageUrl($data['featured_image_url']);
        }
        if (array_key_exists('canonical_url', $data)) {
            $post->setCanonicalUrl($data['canonical_url']);
        }
        if (array_key_exists('code_head', $data)) {
            $post->setCodeHead($data['code_head']);
        }
        if (array_key_exists('code_foot', $data)) {
            $post->setCodeFoot($data['code_foot']);
        }
        if (array_key_exists('published_at', $data)) {
            $post->setPublishedAt($data['published_at']);
        }

        $post->setUpdatedAt($this->now());
        $this->em->flush();

        return $post;
    }

    public function deletePost(Post $post): void
    {
        $this->em->remove($post);
        $this->em->flush();
    }

    /**
     * @param string[] $seoSecondaryKeywords
     */
    public function createPostVariant(
        Post $post,
        Language $language,
        bool $flush = true,
        ?string $content = null,
        ?string $contentUnsaved = null,
        ?string $title = null,
        ?string $description = null,
        ?string $seoPrimaryKeyword = null,
        array $seoSecondaryKeywords = [],
    ): PostVariant {
        $variant = new PostVariant();
        $variant->setPost($post);
        $variant->setLanguage($language);
        $variant->setStatus(PostVariantStatus::DRAFT);
        $variant->setContent($content);
        $variant->setContentUnsaved($contentUnsaved);
        $variant->setTitle($title);
        $variant->setDescription($description);
        $variant->setSeoPrimaryKeyword($seoPrimaryKeyword);
        $variant->setSeoSecondaryKeywords($seoSecondaryKeywords);
        $variant->setCreatedAt($this->now());
        $variant->setUpdatedAt($this->now());
        $this->em->persist($variant);

        if ($flush) {
            $this->em->flush();
        }

        return $variant;
    }

    /**
     * @param array{
     *     slug?: string|null,
     *     status?: PostVariantStatus,
     *     content?: string|null,
     *     content_unsaved?: string|null,
     *     title?: string|null,
     *     description?: string|null,
     *     seo_primary_keyword?: string|null,
     *     seo_secondary_keywords?: string[],
     * } $data
     */
    public function updatePostVariant(
        PostVariant $variant,
        Blog $blog,
        array $data,
        bool $redirectOnSlugChange = false,
    ): PostVariant {
        $oldUrl = $this->permalinkService->getPostPermalink($variant->getPost(), $blog, $variant->getLanguage());

        if (array_key_exists('slug', $data)) {
            $variant->setSlug($data['slug']);
        }

        if (array_key_exists('status', $data)) {
            $status = $data['status'];
            $variant->setStatus($status);

            if ($status === PostVariantStatus::PUBLISHED) {
                $post = $variant->getPost();
                if ($post->getPublishedAt() === null) {
                    $post->setPublishedAt($this->now());
                }

                if ($variant->getSlug() === null) {
                    $title = $variant->getTitle() ?? ($data['title'] ?? null);
                    $slugger = new AsciiSlugger();
                    $slug = $title ? strtolower((string)$slugger->slug($title)) : bin2hex(random_bytes(8));

                    if ($this->getPostByVariantLanguageAndSlug($variant->getLanguage(), $slug) !== null) {
                        $slug = bin2hex(random_bytes(8));
                    }

                    $variant->setSlug($slug);
                }
            }
        }

        if (array_key_exists('content', $data)) {
            $variant->setContent($data['content']);
            $variant->setContentUnsaved(null);
        }

        if (array_key_exists('content_unsaved', $data)) {
            $variant->setContentUnsaved($data['content_unsaved']);
        }

        if (array_key_exists('title', $data)) {
            $title = $data['title'];
            $variant->setTitle($title !== null ? mb_substr($title, 0, 255) : null);
        }

        if (array_key_exists('description', $data)) {
            $desc = $data['description'];
            $variant->setDescription($desc !== null ? mb_substr($desc, 0, 350) : null);
        }

        if (array_key_exists('seo_primary_keyword', $data)) {
            $kw = $data['seo_primary_keyword'];
            $variant->setSeoPrimaryKeyword($kw !== null ? mb_substr($kw, 0, 255) : null);
        }

        if (array_key_exists('seo_secondary_keywords', $data)) {
            $variant->setSeoSecondaryKeywords(array_slice($data['seo_secondary_keywords'], 0, 10));
        }

        $variant->setUpdatedAt($this->now());
        $this->em->flush();

        if ($redirectOnSlugChange) {
            $newUrl = $this->permalinkService->getPostPermalink($variant->getPost(), $blog, $variant->getLanguage());
            $blogUrl = $this->permalinkService->getBlogUrl($blog);

            $oldPath = substr($oldUrl, strlen($blogUrl)) ?: '/';
            $newPath = substr($newUrl, strlen($blogUrl)) ?: '/';

            if ($oldPath !== $newPath && $oldPath !== '') {
                $existingRedirect = $this->redirectService->getRedirectByPath($blog, $oldPath);
                if ($existingRedirect !== null) {
                    $this->redirectService->updateRedirect($existingRedirect, null, $newPath, null);
                } else {
                    $this->redirectService->createRedirect($blog, false, $oldPath, $newPath, RedirectType::PERMANENT);
                }
            }
        }

        return $variant;
    }

    public function deletePostVariant(Post $post, Language $language): void
    {
        $variant = $this->getPostVariantByPostAndLanguage($post, $language);
        if ($variant !== null) {
            $this->em->remove($variant);
            $this->em->flush();
        }
    }

    /**
     * @param Tag[] $tags
     */
    public function setPostTags(Post $post, array $tags, bool $flush = true): void
    {
        $post->getTags()->clear();
        foreach ($tags as $tag) {
            $post->getTags()->add($tag);
        }

        $post->setUpdatedAt($this->now());

        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * @param User[] $users
     */
    public function setPostAuthors(Post $post, array $users, bool $flush = false): void
    {
        $post->getAuthors()->clear();
        foreach ($users as $user) {
            $post->getAuthors()->add($user);
        }

        $post->setUpdatedAt($this->now());

        if ($flush) {
            $this->em->flush();
        }
    }

    public function clonePost(Post $post): Post
    {
        $clone = $this->instantiatePost(
            $post->getBlog(),
            $post->isPage(),
            featuredImageUrl: $post->getFeaturedImageUrl(),
            canonicalUrl: $post->getCanonicalUrl(),
            codeHead: $post->getCodeHead(),
            codeFoot: $post->getCodeFoot(),
        );

        foreach ($post->getVariants() as $variant) {
            $cloneVariant = $this->createPostVariant(
                $clone,
                $variant->getLanguage(),
                flush: false,
                content: $variant->getContent(),
                contentUnsaved: $variant->getContentUnsaved(),
                title: $variant->getTitle(),
                description: $variant->getDescription(),
                seoPrimaryKeyword: $variant->getSeoPrimaryKeyword(),
                seoSecondaryKeywords: $variant->getSeoSecondaryKeywords() ?? [],
            );
            $clone->getVariants()->add($cloneVariant);
        }

        $this->setPostAuthors($clone, array_values($post->getAuthors()->toArray()), flush: false);
        $this->setPostTags($clone, array_values($post->getTags()->toArray()), flush: false);

        $this->em->flush();

        return $clone;
    }

    public function getPostByVariantLanguageAndSlug(Language $language, string $slug): ?Post
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Post::class, 'p')
            ->join(PostVariant::class, 'pv', 'WITH', 'pv.post = p AND pv.language = :language AND pv.slug = :slug')
            ->setParameter('language', $language)
            ->setParameter('slug', $slug)
            ->setMaxResults(1);

        /** @var Post|null $result */
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    public function validateSlug(string $slug): ?string
    {
        foreach (self::SLUG_INVALID_CHARACTERS as $char) {
            if (str_contains($slug, $char)) {
                return $char;
            }
        }
        return null;
    }
}
