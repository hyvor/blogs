<?php

namespace App\Api\Data\Controller;

use App\Api\Data\DataApiHelper;
use App\Api\Data\Factory\PostObjectFactory;
use App\Api\Data\KeysFilter;
use App\Api\Data\Object\PaginationObject;
use App\Api\Data\Resolver\MapBlogFromSubdomain;
use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use App\Service\Delivery\PostQueryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class PostsController
{
    public const ALLOWED_SORTS = [
        'published_at' => 'p.published_at',
        'created_at' => 'p.created_at',
        'id' => 'p.id',
        'updated_at' => 'pv.updated_at',
        'is_featured' => 'p.is_featured',
        'title' => 'pv.title',
        'words' => 'pv.words',
    ];

    public function __construct(
        private DataApiHelper $dataApiHelper,
        private PostQueryService $postQueryService,
        private PostObjectFactory $postObjectFactory,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/post', name: 'post', methods: ['GET'])]
    public function post(#[MapBlogFromSubdomain] Blog $blog, Request $request): JsonResponse
    {
        $id = $request->query->get('id');
        $slug = $request->query->get('slug');
        $languageCode = $request->query->get('language');
        $keys = $request->query->get('keys');

        if ($id === null && $slug === null) {
            throw new UnprocessableEntityHttpException('Either id or slug is required');
        }

        if ($id !== null && !ctype_digit((string)$id)) {
            throw new UnprocessableEntityHttpException('id must be an integer');
        }

        $language = $this->dataApiHelper->getLanguage($blog, is_string($languageCode) ? $languageCode : null);

        $post = null;
        if ($id !== null) {
            $found = $this->postQueryService->getPostById((int)$id);
            if ($found !== null && $found->getBlog()->getId() === $blog->getId()) {
                $post = $found;
            }
        } elseif (is_string($slug)) {
            $post = $this->postQueryService->getPostBySlugAndLanguage($language, $slug);
        }

        if ($post === null) {
            throw new NotFoundHttpException('Post not found');
        }

        $anyVariant = $this->em->getRepository(PostVariant::class)->findOneBy([
            'post' => $post,
            'language' => $language,
        ]);

        if ($anyVariant === null) {
            throw new NotFoundHttpException('Post variant not found');
        }

        if ($anyVariant->getStatus() !== PostVariantStatus::PUBLISHED) {
            throw new UnprocessableEntityHttpException('This post is not published');
        }

        $postObject = $this->postObjectFactory->createFromEntity($post, $blog, $language);

        if ($postObject === null) {
            throw new NotFoundHttpException('Post not found');
        }

        $filtered = KeysFilter::filter($postObject, is_string($keys) ? $keys : null);

        return new JsonResponse($filtered);
    }

    #[Route('/posts', name: 'posts', methods: ['GET'])]
    public function posts(#[MapBlogFromSubdomain] Blog $blog, Request $request): JsonResponse
    {
        $languageCode = $request->query->get('language');
        $limitParam = $request->query->get('limit');
        $pageParam = $request->query->get('page');
        $filter = $request->query->get('filter');
        $sort = $request->query->get('sort');
        $keys = $request->query->get('keys');
        $pages = filter_var($request->query->get('pages', 'false'), FILTER_VALIDATE_BOOLEAN);

        $language = $this->dataApiHelper->getLanguage($blog, is_string($languageCode) ? $languageCode : null);
        $limit = $this->dataApiHelper->getLimit($limitParam !== null ? (int)$limitParam : null);
        $page = $this->dataApiHelper->getPage($pageParam !== null ? (int)$pageParam : null);
        $offset = $this->dataApiHelper->getOffset($page, $limit);
        $orderBys = $this->dataApiHelper->getSort(is_string($sort) ? $sort : null, self::ALLOWED_SORTS);

        $result = $this->postQueryService->getPostsForDataApi(
            $blog,
            $language,
            is_string($filter) ? $filter : null,
            $limit,
            $offset,
            $orderBys,
            (bool)$pages,
        );

        $postObjects = array_map(
            fn($post) => $this->postObjectFactory->createFromEntity($post, $blog, $language),
            $result['posts']
        );
        $postObjects = array_values(array_filter($postObjects));

        $filteredPosts = KeysFilter::filter($postObjects, is_string($keys) ? $keys : null);

        return new JsonResponse([
            'data' => $filteredPosts,
            'pagination' => new PaginationObject($limit, $page, $result['total']),
        ]);
    }

    #[Route('/posts/search', name: 'posts_search', methods: ['GET'])]
    public function search(#[MapBlogFromSubdomain] Blog $blog, Request $request): JsonResponse
    {
        $search = $request->query->get('search');
        if (!is_string($search) || $search === '') {
            throw new UnprocessableEntityHttpException('search parameter is required');
        }

        $languageCode = $request->query->get('language');
        $limitParam = $request->query->get('limit');
        $pageParam = $request->query->get('page');
        $keys = $request->query->get('keys');

        $language = $this->dataApiHelper->getLanguage($blog, is_string($languageCode) ? $languageCode : null);
        $limit = $this->dataApiHelper->getLimit($limitParam !== null ? (int)$limitParam : null);
        $page = $this->dataApiHelper->getPage($pageParam !== null ? (int)$pageParam : null);
        $offset = $this->dataApiHelper->getOffset($page, $limit);

        $result = $this->postQueryService->searchPosts($blog, $language, $search, $limit, $offset);

        $postObjects = array_map(
            fn($post) => $this->postObjectFactory->createFromEntity($post, $blog, $language),
            $result['posts']
        );
        $postObjects = array_values(array_filter($postObjects));

        $filteredPosts = KeysFilter::filter($postObjects, is_string($keys) ? $keys : null);

        return new JsonResponse([
            'data' => $filteredPosts,
            'pagination' => new PaginationObject($limit, $page, $result['total']),
        ]);
    }
}
