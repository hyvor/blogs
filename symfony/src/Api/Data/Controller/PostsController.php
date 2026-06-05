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
use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use App\Service\Delivery\PostQueryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
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
    public function post(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetPostInput $input): JsonResponse
    {
        if ($input->id === null && $input->slug === null) {
            throw new UnprocessableEntityHttpException('Either id or slug is required');
        }

        $language = $this->dataApiHelper->getLanguage($blog, $input->language);

        $post = null;
        if ($input->id !== null) {
            $found = $this->postQueryService->getPostById($input->id);
            if ($found !== null && $found->getBlog()->getId() === $blog->getId()) {
                $post = $found;
            }
        } elseif ($input->slug !== null) {
            $post = $this->postQueryService->getPostBySlugAndLanguage($language, $input->slug);
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

        $result = $this->postQueryService->getPostsForDataApi(
            $blog,
            $language,
            $input->filter,
            $limit,
            $offset,
            $orderBys,
            $input->pages ?? false,
        );

        $postObjects = array_map(
            fn($post) => $this->postObjectFactory->createFromEntity($post, $blog, $language),
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

        $result = $this->postQueryService->searchPosts($blog, $language, $input->search, $limit, $offset);

        $postObjects = array_map(
            fn($post) => $this->postObjectFactory->createFromEntity($post, $blog, $language),
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
