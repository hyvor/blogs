<?php

namespace App\Api\Data\Controller;

use App\Api\Data\DataApiHelper;
use App\Api\Data\Factory\AuthorObjectFactory;
use App\Api\Data\Input\GetAuthorInput;
use App\Api\Data\Input\GetAuthorsInput;
use App\Api\Data\KeysFilter;
use App\Api\Data\Object\PaginationObject;
use App\Api\Data\Resolver\MapBlogFromSubdomain;
use App\Entity\Blog;
use App\Entity\User;
use App\Service\User\UserService;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class AuthorsController
{
    public const ALLOWED_SORTS = [
        'posts_count' => 'u.posts_count',
        'created_at' => 'u.created_at',
    ];

    public function __construct(
        private DataApiHelper $dataApiHelper,
        private AuthorObjectFactory $authorObjectFactory,
        private UserService $userService,
    ) {}

    #[Route('/author', name: 'author', methods: ['GET'])]
    public function author(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetAuthorInput $input): JsonResponse
    {
        if ($input->id === null && $input->slug === null) {
            throw new UnprocessableEntityHttpException('Either id or slug is required');
        }

        $language = $this->dataApiHelper->getLanguage($blog, $input->language);

        $user = null;
        if ($input->id !== null) {
            $user = $this->userService->getUserById($blog, $input->id);
        } elseif ($input->slug !== null) {
            $user = $this->userService->getUserBySlug($blog, $input->slug);
        }

        if ($user === null) {
            throw new NotFoundHttpException('Author not found');
        }

        // must have written one post to be an author
        // otherwise, it can be a user like finance
        if ($user->getPostsCount() === 0) {
            throw new UnprocessableEntityHttpException('User is not an author');
        }

        $authorObject = $this->authorObjectFactory->create($user, $blog, $language);

        $filtered = KeysFilter::filter($authorObject, $input->keys);

        return new JsonResponse($filtered);
    }

    #[Route('/authors', name: 'authors', methods: ['GET'])]
    public function authors(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetAuthorsInput $input): JsonResponse
    {
        $language = $this->dataApiHelper->getLanguage($blog, $input->language);
        $limit = $this->dataApiHelper->getLimit($input->limit);
        $page = $this->dataApiHelper->getPage($input->page);
        $offset = $this->dataApiHelper->getOffset($page, $limit);
        $orderBys = $this->dataApiHelper->getSort($input->sort, self::ALLOWED_SORTS);

        try {
            $result = $this->userService->getAuthorsWithFilterQ($blog, $input->filter, $limit, $offset, $orderBys);
        } catch (FilterQException $e) {
            throw new UnprocessableEntityHttpException("Filter error: " . $e->getMessage(), $e);
        }

        $authorObjects = array_map(
            fn(User $user) => $this->authorObjectFactory->create($user, $blog, $language),
            $result['users']
        );

        $filteredAuthors = KeysFilter::filter($authorObjects, $input->keys);

        return new JsonResponse([
            'data' => $filteredAuthors,
            'pagination' => new PaginationObject($limit, $page, $result['total']),
        ]);
    }
}
