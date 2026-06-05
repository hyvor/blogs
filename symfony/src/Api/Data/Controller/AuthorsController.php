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
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
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
        private EntityManagerInterface $em,
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
            $user = $this->em->getRepository(User::class)->findOneBy(['id' => $input->id, 'blog' => $blog]);
        } elseif ($input->slug !== null) {
            $user = $this->em->getRepository(User::class)->findOneBy(['slug' => $input->slug, 'blog' => $blog]);
        }

        if ($user === null) {
            throw new NotFoundHttpException('Author not found');
        }

        if ($user->getPostsCount() === 0) {
            throw new UnprocessableEntityHttpException('User is not an author');
        }

        $authorObject = $this->authorObjectFactory->createFromEntity($user, $blog, $language);

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

        [$authors, $total] = $this->queryAuthors($blog, $input->filter, $limit, $offset, $orderBys);

        $authorObjects = array_map(
            fn(User $user) => $this->authorObjectFactory->createFromEntity($user, $blog, $language),
            $authors
        );

        $filteredAuthors = KeysFilter::filter($authorObjects, $input->keys);

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
