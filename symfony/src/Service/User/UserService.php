<?php

namespace App\Service\User;

use App\Entity\Blog;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Post\PostAuthor\PostAuthorService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
use Hyvor\Internal\Auth\AuthUser;

class UserService
{

    public function __construct(
        private EntityManagerInterface $em,
        private PostAuthorService $postAuthorService,
        private UserRepository $userRepository,
    ) {}

    public function getUserByBlogAndAuthUser(Blog $blog, AuthUser|int $authUserOrId): ?User
    {
        $authUserId = $authUserOrId instanceof AuthUser ? $authUserOrId->id : $authUserOrId;

        /** @var User|null */
        return $this->userRepository->findOneBy([
            'blog' => $blog,
            'hyvor_user_id' => $authUserId,
            'status' => 'active',
        ]);
    }

    public function getUserById(Blog $blog, int $id): ?User
    {
        /** @var User|null */
        return $this->userRepository->findOneBy(['id' => $id, 'blog' => $blog]);
    }

    public function getUserBySlug(Blog $blog, string $slug): ?User
    {
        /** @var User|null */
        return $this->userRepository->findOneBy(['slug' => $slug, 'blog' => $blog]);
    }

    /**
     * @param array<array{0: string, 1: string}> $orderBys
     * @return array{users: User[], total: int}
     * @throws FilterQException
     */
    public function getAuthorsWithFilterQ(
        Blog $blog,
        ?string $filter,
        int $limit,
        int $offset,
        array $orderBys,
    ): array {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u')
            ->where('u.blog = :blog')
            ->andWhere('u.posts_count > 0')
            ->setParameter('blog', $blog);

        if ($filter !== null && $filter !== '') {
            FilterQ::expression($filter)
                ->queryBuilder($qb)
                ->keys(function ($keys) {
                    $keys->add('id', 'u.id')->valueType('int');
                    $keys->add('slug', 'u.slug')->valueType('string');
                    $keys->add('posts_count', 'u.posts_count')->valueType('int');
                    $keys->add('created_at', 'u.created_at')->valueType('date');
                })
                ->addWhere();
        }

        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT u.id)');
        $totalFetch = $countQb->getQuery()->getSingleScalarResult();
        $total = is_numeric($totalFetch) ? (int) $totalFetch : 0;

        if ($total === 0) {
            return ['users' => [], 'total' => 0];
        }

        foreach ($orderBys as [$column, $direction]) {
            $qb->addOrderBy($column, $direction);
        }

        $qb->setMaxResults($limit)->setFirstResult($offset);

        /** @var User[] $users */
        $users = $qb->getQuery()->getResult();

        return ['users' => $users, 'total' => $total];
    }

    /**
     * @return User[]
     */
    public function getBlogsForUser(int $hyvorUserId, int $organizationId): array
    {
        /** @var User[] */
        return $this->userRepository
            ->createQueryBuilder('u')
            ->join('u.blog', 'b')
            ->leftJoin('b.variants', 'bv')
            ->addSelect('b', 'bv')
            ->where('u.hyvor_user_id = :userId')
            ->andWhere('b.organization_id = :orgId')
            ->andWhere('u.status = :status')
            ->setParameter('userId', $hyvorUserId)
            ->setParameter('orgId', $organizationId)
            ->setParameter('status', 'active')
            ->orderBy('u.sort', 'ASC')
            ->addOrderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $blogIds ordered list of blog IDs to set sort order
     */
    public function changeBlogSorts(int $hyvorUserId, array $blogIds): void
    {
        $i = 1;
        foreach ($blogIds as $blogId) {
            $this->em->getConnection()->executeStatement(
                'UPDATE users SET sort = ? WHERE blog_id = ? AND hyvor_user_id = ?',
                [$i, $blogId, $hyvorUserId],
            );
            $i++;
        }
    }

    /**
     * Might be able to use getUserByBlogAndAuthUser instead
     */
    public function hasAccessToBlog(Blog $blog, int $hyvorUserId): bool
    {
        $user = $this->em->getRepository(User::class)->findOneBy([
            'blog' => $blog,
            'hyvor_user_id' => $hyvorUserId,
        ]);

        if (!$user) {
            return false;
        }

        return true;
    }

    /**
     * Note: this does not emit events like Laravel does, which means
     * counts are not updated and webhooks are not triggered
     */
    public function deleteUser(User $user): void
    {
        $this->em->wrapInTransaction(function () use ($user) {
            foreach ($user->getVariants() as $variant) {
                $this->em->remove($variant);
            }

            $this->postAuthorService->deleteByUser($user);

            $this->em->remove($user);
        });
    }
}
