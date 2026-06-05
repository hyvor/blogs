<?php

namespace App\Api\Data\Controller;

use App\Api\Data\DataApiHelper;
use App\Api\Data\Factory\AuthorObjectFactory;
use App\Api\Data\KeysFilter;
use App\Api\Data\Object\PaginationObject;
use App\Api\Data\Resolver\MapBlogFromSubdomain;
use App\Entity\Blog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        private EntityManagerInterface $em,
    ) {}

    #[Route('/author', name: 'author', methods: ['GET'])]
    public function author(#[MapBlogFromSubdomain] Blog $blog, Request $request): JsonResponse
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

        $user = null;
        if ($id !== null) {
            $user = $this->em->getRepository(User::class)->findOneBy(['id' => (int)$id, 'blog' => $blog]);
        } elseif (is_string($slug)) {
            $user = $this->em->getRepository(User::class)->findOneBy(['slug' => $slug, 'blog' => $blog]);
        }

        if ($user === null) {
            throw new NotFoundHttpException('Author not found');
        }

        if ($user->getPostsCount() === 0) {
            throw new UnprocessableEntityHttpException('User is not an author');
        }

        $authorObject = $this->authorObjectFactory->createFromEntity($user, $blog, $language);

        $filtered = KeysFilter::filter($authorObject, is_string($keys) ? $keys : null);

        return new JsonResponse($filtered);
    }

    #[Route('/authors', name: 'authors', methods: ['GET'])]
    public function authors(#[MapBlogFromSubdomain] Blog $blog, Request $request): JsonResponse
    {
        $languageCode = $request->query->get('language');
        $limitParam = $request->query->get('limit');
        $pageParam = $request->query->get('page');
        $filter = $request->query->get('filter');
        $sort = $request->query->get('sort');
        $keys = $request->query->get('keys');

        $language = $this->dataApiHelper->getLanguage($blog, is_string($languageCode) ? $languageCode : null);
        $limit = $this->dataApiHelper->getLimit($limitParam !== null ? (int)$limitParam : null);
        $page = $this->dataApiHelper->getPage($pageParam !== null ? (int)$pageParam : null);
        $offset = $this->dataApiHelper->getOffset($page, $limit);
        $orderBys = $this->dataApiHelper->getSort(is_string($sort) ? $sort : null, self::ALLOWED_SORTS);

        [$authors, $total] = $this->queryAuthors($blog, is_string($filter) ? $filter : null, $limit, $offset, $orderBys);

        $authorObjects = array_map(
            fn(User $user) => $this->authorObjectFactory->createFromEntity($user, $blog, $language),
            $authors
        );

        $filteredAuthors = KeysFilter::filter($authorObjects, is_string($keys) ? $keys : null);

        return new JsonResponse([
            'data' => $filteredAuthors,
            'pagination' => new PaginationObject($limit, $page, $total),
        ]);
    }

    /**
     * @param array<array{0: string, 1: string}> $orderBys
     * @return array{0: User[], 1: int}
     */
    private function queryAuthors(Blog $blog, ?string $filter, int $limit, int $offset, array $orderBys): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u')
            ->where('u.blog = :blog')
            ->andWhere('u.posts_count > 0')
            ->setParameter('blog', $blog);

        if ($filter !== null && $filter !== '') {
            try {
                FilterQ::expression($filter)
                    ->queryBuilder($qb)
                    ->keys(function ($keys) {
                        $keys->add('id', 'u.id')->valueType('int');
                        $keys->add('slug', 'u.slug')->valueType('string');
                        $keys->add('posts_count', 'u.posts_count')->valueType('int');
                        $keys->add('created_at', 'u.created_at')->valueType('date');
                    })
                    ->addWhere();
            } catch (FilterQException $e) {
                throw new UnprocessableEntityHttpException($e->getMessage(), $e);
            }
        }

        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT u.id)');
        $totalFetch = $countQb->getQuery()->getSingleScalarResult();
        $total = is_numeric($totalFetch) ? (int)$totalFetch : 0;

        if ($total === 0) {
            return [[], 0];
        }

        foreach ($orderBys as [$column, $direction]) {
            $qb->addOrderBy($column, $direction);
        }

        $qb->setMaxResults($limit)->setFirstResult($offset);

        /** @var User[] $users */
        $users = $qb->getQuery()->getResult();

        return [$users, $total];
    }
}
