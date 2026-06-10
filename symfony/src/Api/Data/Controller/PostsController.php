<?php

namespace App\Api\Data\Controller;

use App\Api\Data\DataApiHelper;
use App\Api\Data\Factory\PostObjectFactory;
use App\Api\Data\Input\GetPostInput;
use App\Api\Data\Input\GetPostsInput;
use App\Api\Data\Input\SearchPostsInput;
use App\Api\Data\KeysFilter;
use App\Api\Data\Object\PaginationObject;
use App\Api\Data\Resolver\MapBlogFromSubdomain;
use App\Entity\Blog;
use App\Service\Post\PostService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Enum\PostVariantStatus;

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
        private PostService $postService,
        private PostObjectFactory $postObjectFactory,
    ) {}

    #[Route('/post', name: 'post', methods: ['GET'])]
    public function post(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetPostInput $input): JsonResponse
    {
        if ($input->id === null && $input->slug === null) {
            throw new UnprocessableEntityHttpException('Either id or slug is required');
        }

        $language = $this->dataApiHelper->getLanguage($blog, $input->language);

        $post = null;
        if ($input->id !== null) {
            $post = $this->postService->getPostByBlogAndId($blog, $input->id);
        } elseif ($input->slug !== null) {
            $post = $this->postService->getPostBySlugAndLanguage($language, $input->slug);
        }

        if ($post === null) {
            throw new NotFoundHttpException('Post not found');
        }

        $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);

        if ($variant === null) {
            throw new NotFoundHttpException('Post not found: variant for language not found');
        }

        if ($variant->getStatus() !== PostVariantStatus::PUBLISHED) {
            throw new NotFoundHttpException('Post not found: not published');
        }

        $postObject = $this->postObjectFactory->create($post, $variant, $blog, $language);

        $filtered = KeysFilter::filter($postObject, $input->keys);

        return new JsonResponse($filtered);
    }

    #[Route('/posts', name: 'posts', methods: ['GET'])]
    public function posts(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetPostsInput $input): JsonResponse
    {
        $language = $this->dataApiHelper->getLanguage($blog, $input->language);
        $limit = $this->dataApiHelper->getLimit($input->limit);
        $page = $this->dataApiHelper->getPage($input->page);
        $offset = $this->dataApiHelper->getOffset($page, $limit);
        $orderBys = $this->dataApiHelper->getSort($input->sort, self::ALLOWED_SORTS);

        $result = $this->postService->getPostsForDataApi(
            $blog,
            $language,
            $input->filter,
            $limit,
            $offset,
            $orderBys,
            $input->pages ?? false,
        );

        $postObjects = array_map(
            function ($post) use ($blog, $language) {
                $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);
                if ($variant === null) {
                    return null;
                }
                return $this->postObjectFactory->create($post, $variant, $blog, $language);
            },
            $result['posts']
        );
        $postObjects = array_values(array_filter($postObjects));

        $filteredPosts = KeysFilter::filter($postObjects, $input->keys);

        return new JsonResponse([
            'data' => $filteredPosts,
            'pagination' => new PaginationObject($limit, $page, $result['total']),
        ]);
    }

    #[Route('/posts/search', name: 'posts_search', methods: ['GET'])]
    public function search(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] SearchPostsInput $input): JsonResponse
    {
        $language = $this->dataApiHelper->getLanguage($blog, $input->language);
        $limit = $this->dataApiHelper->getLimit($input->limit);
        $page = $this->dataApiHelper->getPage($input->page);
        $offset = $this->dataApiHelper->getOffset($page, $limit);

        $result = $this->postService->searchPosts($blog, $language, $input->search, $limit, $offset);

        $postObjects = array_map(
            function ($post) use ($blog, $language) {
                $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);
                if ($variant === null) {
                    return null;
                }
                return $this->postObjectFactory->create($post, $variant, $blog, $language);
            },
            $result['posts']
        );
        $postObjects = array_values(array_filter($postObjects));

        $filteredPosts = KeysFilter::filter($postObjects, $input->keys);

        return new JsonResponse([
            'data' => $filteredPosts,
            'pagination' => new PaginationObject($limit, $page, $result['total']),
        ]);
    }
}
