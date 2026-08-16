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
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Event\PostCreatedEvent;
use App\Service\Post\Event\PostDeletedEvent;
use App\Service\Post\Event\PostUpdatedEvent;
use App\Service\Post\Event\PostVariantCreatedEvent;
use App\Service\Post\Event\PostVariantDeletedEvent;
use App\Service\Post\Event\PostVariantPublishedEvent;
use App\Service\Post\Event\PostVariantUnpublishedEvent;
use App\Service\Post\Event\PostVariantUpdatedEvent;
use App\Service\Post\Suggestion\PostSuggestionContentChecker;
use App\Service\Redirect\RedirectService;
use App\Service\Route\PermalinkService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as OrmQB;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PostService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private Connection $connection,
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private RedirectService $redirectService,
        private EventDispatcherInterface $ed,
        private PostSlugService $postSlugService,
        private PostContentService $postContentService,
        private PostSuggestionContentChecker $postSuggestionContentChecker,
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

    /**
     * @param int[] $ids
     * @return Post[]
     */
    public function getPostsByIds(array $ids): array
    {
        return $this->em->getRepository(Post::class)->findBy(['id' => $ids]);
    }

    public function getPublishedPostBySlugAndLanguage(Language $language, string $slug): ?Post
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

    public function getPostVariantByBlogAndId(Blog $blog, int $id): ?PostVariant
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('pv')
            ->from(PostVariant::class, 'pv')
            ->join(Post::class, 'p', 'WITH', 'pv.post = p AND p.blog = :blog')
            ->where('pv.id = :id')
            ->setParameter('blog', $blog)
            ->setParameter('id', $id)
            ->setMaxResults(1);

        /** @var PostVariant|null $result */
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
     * @throws FilterQException
     */
    public function getPostsWithFilterQ(
        Blog $blog,
        Language $language,
        ?string $filter,
        int $limit = 10,
        int $offset = 0,
        bool $featuredFirst = true,
        bool $isPage = false,
        ?array $orderBys = null
    ): array {

        $orderBys ??= $featuredFirst
            ? [['p.is_featured', 'DESC'], ['p.published_at', 'DESC']]
            : [['p.published_at', 'DESC']];

        $qb = $this->em->createQueryBuilder();
        $qb->from(Post::class, 'p')
            ->join(PostVariant::class, 'pv', 'WITH', 'pv.post = p AND pv.language = :language AND pv.status = :status')
            ->where('p.blog = :blog')
            ->andWhere('p.is_page = :isPage')
            ->setParameter('blog', $blog)
            ->setParameter('language', $language)
            ->setParameter('status', PostVariantStatus::PUBLISHED)
            ->setParameter('isPage', $isPage);

        if ($filter) {
            FilterQ::expression($filter)
                ->queryBuilder($qb)
                ->keys(function (\Hyvor\FilterQ\Keys $keys) {
                    $keys->add('id', 'p.id')->valueType('int');
                    $keys->add('published_at', 'p.published_at')->valueType('date');
                    $keys->add('created_at', 'p.created_at')->valueType('date');
                    $keys->add('updated_at', 'pv.updated_at')->valueType('date');
                    $keys->add('is_featured', 'p.is_featured')->valueType('bool')->operators('=,!=');
                    $keys->add('slug', 'pv.slug')->valueType('string')->operators('=,!=');
                    $keys->add('words', 'pv.words')->valueType('int');
                    $keys->add('featured_image_url', 'p.featured_image_url')->valueType(['string', 'null'])->operators('=,!=');
                    $keys->add('canonical_url', 'p.canonical_url')->valueType(['string', 'null'])->operators('=,!=');

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
        }

        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT p.id)');
        $totalFetch = $countQb->getQuery()->getSingleScalarResult();
        $total = is_numeric($totalFetch) ? (int)$totalFetch : 0;


        if ($total === 0) {
            return ['posts' => [], 'total' => 0];
        }

        foreach ($orderBys as [$column, $direction]) {
            $qb->addOrderBy($column, $direction);
        }

        $postIdRows = $qb->setMaxResults($limit)
            // need to select the columns in the WHERE clause
            ->select('DISTINCT p.id as pid, pv.title, pv.words, pv.updated_at, p.is_featured, p.published_at, p.created_at')
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();

        $postIds = array_column($postIdRows, 'pid');

        if (empty($postIds)) {
            return ['posts' => [], 'total' => $total];
        }

        $posts = $this->getPostsByIds($postIds);

        /** @var array<int|string, int> $idOrder */
        $idOrder = array_flip($postIds);
        usort($posts, fn($a, $b) => ($idOrder[$a->getId()] ?? 0) <=> ($idOrder[$b->getId()] ?? 0));

        return ['posts' => $posts, 'total' => $total];
    }

    /**
     * @return array{posts: Post[], total: int}
     */
    public function getPosts(
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
        ?int $id = null,
        ?string $slug = null,
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

        if ($id !== null) {
            $where .= ' AND p.id = :id';
            $params['id'] = $id;
        }

        if ($slug !== null) {
            $where .= ' AND pv.slug = :slug';
            $params['slug'] = $slug;
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
    public function getPostsForExport(Blog $blog, int $limit, int $offset): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Post::class, 'p')
            ->where('p.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        /** @var Post[] */
        return $qb->getQuery()->getResult();
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
        ?\DateTimeImmutable $publishedAt = null,
        // disable creating the variant, only makes sense in BlogCreator
        // be careful when set to false, if the variant is not set manually, it could cause data inconsistency
        bool $createVariant = true,
        bool $flush = true,
    ): Post {

        $post = $this->instantiatePost(
            $blog,
            $isPage,
            $isFeatured,
            $featuredImageUrl,
            $canonicalUrl,
            $codeHead,
            $codeFoot,
            $publishedAt,
        );

        if ($createVariant) {
            $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
            $variant = $this->createPostVariant($post, $primaryLanguage, flush: false);
            $post->getVariants()->add($variant);
        }

        $this->setPostAuthors($post, $authors, flush: false);

        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch(new PostCreatedEvent($post));
        }

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
        ?\DateTimeImmutable $publishedAt = null,
    ): Post {
        $post = new Post();
        $post->setBlog($blog);
        $post->setIsPage($isPage);
        $post->setIsFeatured($isFeatured);
        $post->setFeaturedImageUrl($featuredImageUrl);
        $post->setCanonicalUrl($canonicalUrl);
        $post->setCodeHead($codeHead);
        $post->setCodeFoot($codeFoot);
        $post->setPublishedAt($publishedAt);
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

        $this->ed->dispatch(new PostUpdatedEvent($post));

        return $post;
    }

    public function deletePost(Post $post): void
    {
        $this->em->remove($post);
        $this->em->flush();

        $this->ed->dispatch(new PostDeletedEvent($post));
    }

    /**
     * @param string[] $seoSecondaryKeywords
     * @param array<string, number>|null $linkAnalysis
     */
    public function createPostVariant(
        Post $post,
        Language $language,
        bool $flush = true,
        PostVariantStatus $status = PostVariantStatus::DRAFT,
        ?string $content = null,
        ?string $contentUnsaved = null,
        ?string $slug = null,
        ?string $title = null,
        ?string $description = null,
        ?string $seoPrimaryKeyword = null,
        array $seoSecondaryKeywords = [],
        ?array $linkAnalysis = null,
    ): PostVariant {
        $variant = new PostVariant();
        $variant->setPost($post);
        $variant->setLanguage($language);
        $variant->setStatus($status);
        $variant->setContent($content);
        $variant->setContentUnsaved($contentUnsaved);
        $variant->setSlug($slug);
        $variant->setTitle($title);
        $variant->setDescription($description);
        $variant->setSeoPrimaryKeyword($seoPrimaryKeyword);
        $variant->setSeoSecondaryKeywords($seoSecondaryKeywords);
        $variant->setLinkAnalysis($linkAnalysis);
        $variant->setCreatedAt($this->now());
        $variant->setUpdatedAt($this->now());

        if ($status === PostVariantStatus::PUBLISHED || $status === PostVariantStatus::SCHEDULED) {
            if ($post->getPublishedAt() === null) {
                $post->setPublishedAt($this->now());
            }
            $variant->setContentUpdatedAt($post->getPublishedAt());
        }

        if ($status === PostVariantStatus::PUBLISHED) {
            assert($variant->getSlug() !== null, 'Slug must be set for published post variant');
        }

        $this->em->persist($variant);

        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch(new PostVariantCreatedEvent($variant));
        }

        return $variant;
    }

    /**
     * @param array{
     *     slug?: string|null,
     *     content?: string|null,
     *     content_unsaved?: string|null,
     *     title?: string|null,
     *     description?: string|null,
     *     seo_primary_keyword?: string|null,
     *     seo_secondary_keywords?: string[],
     *     link_analysis?: array<string, number>,
     *     content_updated_at?: \DateTimeImmutable|null,
     * } $data
     */
    public function updatePostVariant(
        PostVariant $variant,
        Blog $blog,
        array $data,
        bool $redirectOnSlugChange = false,
    ): PostVariant {
        $oldUrl = $this->permalinkService->getPostVariantPermalink($variant);

        // `content` is only guarded once it's public: a draft's `content` is edited
        // continuously (autosave), but for a published/scheduled variant, `content` is
        // only ever set here via the "Update" flow (content_unsaved -> content), i.e.
        // the moment it actually goes live - see publishPostVariant() for the other
        // (draft -> published) transition that needs the same guard.
        if (
            array_key_exists('content', $data) &&
            $variant->getStatus() !== PostVariantStatus::DRAFT &&
            $this->postSuggestionContentChecker->hasPendingSuggestions($data['content'])
        ) {
            throw new UnprocessableEntityHttpException(
                'This post has unresolved suggestions or comments. Resolve them before publishing.',
            );
        }

        if (array_key_exists('slug', $data)) {
            $variant->setSlug($data['slug']);
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

        if (array_key_exists('link_analysis', $data)) {
            $variant->setLinkAnalysis($data['link_analysis']);
        }

        $isDraft = $variant->getStatus() === PostVariantStatus::DRAFT;

        if (array_key_exists('content_updated_at', $data)) {
            $contentUpdatedAt = $data['content_updated_at'];
            $this->assertContentUpdatedAtValid($variant, $contentUpdatedAt);
            $variant->setContentUpdatedAt($contentUpdatedAt);
        } elseif (array_key_exists('content', $data) && !$isDraft) {
            $variant->setContentUpdatedAt($this->now());
        }

        $variant->setUpdatedAt($this->now());
        $this->em->flush();

        if ($redirectOnSlugChange) {
            $newUrl = $this->permalinkService->getPostVariantPermalink($variant);
            $blogUrl = $this->permalinkService->getBlogUrl($blog);

            $oldPath = substr($oldUrl, strlen($blogUrl)) ?: '/';
            $newPath = substr($newUrl, strlen($blogUrl)) ?: '/';

            if ($oldPath !== $newPath) {
                $existingRedirect = $this->redirectService->getRedirectByPath($blog, $oldPath);
                if ($existingRedirect !== null) {
                    $this->redirectService->updateRedirect($existingRedirect, null, $newPath, null);
                } else {
                    $this->redirectService->createRedirect($blog, false, $oldPath, $newPath, RedirectType::PERMANENT);
                }
            }
        }

        $this->ed->dispatch(new PostVariantUpdatedEvent($variant));

        return $variant;
    }

    public function publishPostVariant(PostVariant $variant, Blog $blog): PostVariant
    {
        if ($this->postSuggestionContentChecker->hasPendingSuggestions($variant->getContent())) {
            throw new UnprocessableEntityHttpException(
                'This post has unresolved suggestions or comments. Resolve them before publishing.',
            );
        }

        if ($variant->getSlug() === null) {
            $slug = $this->postSlugService->generateUniqueSlug($variant->getLanguage(), $variant->getTitle());
            $variant->setSlug($slug);
        }

        $post = $variant->getPost();
        if ($post->getPublishedAt() === null) {
            $post->setPublishedAt($this->now());
        }

        $variant->setStatus(PostVariantStatus::PUBLISHED);
        $variant->setContentUpdatedAt($post->getPublishedAt());
        $variant->setUpdatedAt($this->now());
        $this->em->flush();

        $this->ed->dispatch(new PostVariantUpdatedEvent($variant));
        $this->ed->dispatch(new PostVariantPublishedEvent($variant));

        return $variant;
    }

    public function unpublishPostVariant(PostVariant $variant): PostVariant
    {
        $variant->setStatus(PostVariantStatus::DRAFT);
        $variant->setContentUpdatedAt(null);
        $variant->setUpdatedAt($this->now());
        $this->em->flush();

        $this->ed->dispatch(new PostVariantUpdatedEvent($variant));
        $this->ed->dispatch(new PostVariantUnpublishedEvent($variant));

        return $variant;
    }

    private function assertContentUpdatedAtValid(PostVariant $variant, ?\DateTimeImmutable $contentUpdatedAt): void
    {
        if ($contentUpdatedAt === null) {
            return;
        }

        $publishedAt = $variant->getPost()->getPublishedAt();
        if ($publishedAt !== null && $contentUpdatedAt < $publishedAt) {
            throw new UnprocessableEntityHttpException('content_updated_at must be greater than or equal to published_at');
        }
    }

    public function deletePostVariant(Post $post, Language $language): void
    {
        $variant = $this->getPostVariantByPostAndLanguage($post, $language);
        if ($variant !== null) {
            $this->em->remove($variant);
            $this->em->flush();

            $this->ed->dispatch(new PostVariantDeletedEvent($variant));
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
    public function setPostAuthors(Post $post, array $users, bool $flush = true): void
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
                linkAnalysis: $variant->getLinkAnalysis(),
            );
            $clone->getVariants()->add($cloneVariant);
        }

        $this->setPostAuthors($clone, array_values($post->getAuthors()->toArray()), flush: false);
        $this->setPostTags($clone, array_values($post->getTags()->toArray()), flush: false);

        $this->em->flush();

        return $clone;
    }

    public function getPostVariantByLanguageAndSlug(Language $language, string $slug): ?PostVariant
    {
        return $this->em->getRepository(PostVariant::class)->findOneBy([
            'language' => $language,
            'slug' => $slug,
        ]);
    }

    public function renderPostVariantHtml(PostVariant $variant): void
    {
        if (!$variant->getContent()) {
            return;
        }

        $blog = $variant->getPost()->getBlog();

        $html = $this->postContentService->getHtml($variant->getContent(), $blog);
        $text = $this->postContentService->getText($variant->getContent(), $blog);

        $variant->setContentHtml($html);
        $variant->setContentText($text);
    }

    private const string PREVIEW_ID_LETTERS = 'abcdefghijklmnopqrstuvwxyz123456789';

    public function getPreviewId(int|Post $idOrPost): string
    {
        $id = $idOrPost instanceof Post ? $idOrPost->getId() : $idOrPost;
        $length = strlen(self::PREVIEW_ID_LETTERS);
        $s = '';
        while ($id > 0) {
            $s = self::PREVIEW_ID_LETTERS[$id % $length] . $s;
            $id = intdiv($id, $length);
        }
        return $s;
    }

    public function parsePreviewId(string $previewId): ?int
    {
        $length = strlen(self::PREVIEW_ID_LETTERS);
        $id = 0;
        for ($i = 0; $i < strlen($previewId); $i++) {
            $char = $previewId[$i];
            $pos = strpos(self::PREVIEW_ID_LETTERS, $char);
            if ($pos === false) {
                return null;
            }
            $id = $id * $length + $pos;
        }
        return $id;
    }
}
