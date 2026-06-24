<?php

namespace App\Service\User;

use App\Entity\Blog;
use App\Entity\Enum\UserRole;
use App\Entity\Enum\UserStatus;
use App\Entity\Language;
use App\Entity\User;
use App\Entity\UserVariant;
use App\Repository\UserRepository;
use App\Service\Language\LanguageService;
use App\Service\Media\MediaService;
use App\Service\Media\MediaUploadException;
use App\Service\Post\PostAuthor\PostAuthorService;
use App\Service\Route\PermalinkService;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserUpdatedEvent;
use App\Service\User\Event\UserVariantCreatedEvent;
use App\Service\User\Event\UserVariantDeletedEvent;
use App\Service\User\Event\UserVariantUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class UserService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private PostAuthorService $postAuthorService,
        private UserRepository $userRepository,
        private LanguageService $languageService,
        private EventDispatcherInterface $ed,
        private AuthInterface $auth,
        private MediaService $mediaService,
        private PermalinkService $permalinkService,
    ) {}

    public function getUserByBlogAndAuthUser(Blog $blog, AuthUser|int $authUserOrId): ?User
    {
        $authUserId = $authUserOrId instanceof AuthUser ? $authUserOrId->id : $authUserOrId;

        /** @var User|null */
        return $this->userRepository->findOneBy([
            'blog' => $blog,
            'hyvor_user_id' => $authUserId,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    public function getUserById(Blog $blog, int $id): ?User
    {
        /** @var User|null */
        return $this->userRepository->findOneBy(['id' => $id, 'blog' => $blog]);
    }

    public function getUserByHyvorUserId(Blog $blog, int $hyvorUserId): ?User
    {
        /** @var User|null */
        return $this->userRepository->findOneBy(['blog' => $blog, 'hyvor_user_id' => $hyvorUserId]);
    }

    /**
     * @return User[]
     */
    public function searchUsers(Blog $blog, string $search, int $limit = 10): array
    {
        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        $search = str_replace('%', '', $search);

        /** @var User[] */
        return $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->join('u.variants', 'uv')
            ->where('u.blog = :blog')
            ->andWhere('uv.language = :language')
            ->andWhere('uv.name LIKE :search')
            ->setParameter('blog', $blog)
            ->setParameter('language', $primaryLanguage)
            ->setParameter('search', $search . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $ids
     * @return User[]
     */
    public function getUsersByIds(Blog $blog, array $ids): array
    {
        /** @var User[] */
        return $this->userRepository->findBy(['id' => $ids, 'blog' => $blog]);
    }

    public function getUserBySlug(Blog $blog, string $slug): ?User
    {
        /** @var User|null */
        return $this->userRepository->findOneBy(['slug' => $slug, 'blog' => $blog]);
    }

    /**
     * @return User[]
     */
    public function getUsers(Blog $blog, int $limit, int $offset = 0): array
    {
        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);

        /** @var User[] */
        return $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->join('u.variants', 'uv')
            ->where('u.blog = :blog')
            ->andWhere('uv.language = :language')
            ->setParameter('blog', $blog)
            ->setParameter('language', $primaryLanguage)
            ->orderBy('CASE WHEN u.role = :ownerRole THEN 0 ELSE 1 END', 'ASC')
            ->addOrderBy('u.posts_count', 'DESC')
            ->setParameter('ownerRole', UserRole::OWNER)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
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
            ->setParameter('status', UserStatus::ACTIVE)
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

    public function deleteUser(User $user): void
    {
        $this->em->wrapInTransaction(function () use ($user) {
            foreach ($user->getVariants()->toArray() as $variant) {
                $this->deleteUserVariant($variant);
            }

            $this->postAuthorService->deleteByUser($user);

            $this->em->remove($user);
        });

        $this->ed->dispatch(new UserDeletedEvent($user));
    }

    /** @throws \Exception if the Hyvor user is not found */
    public function createUserFromHyvorUser(Blog $blog, int $hyvorUserId, UserRole $role): User
    {
        $hyvorUser = $this->auth->fromId($hyvorUserId);

        if ($hyvorUser === null) {
            throw new \Exception('User not found');
        }

        $now = $this->now();

        $pictureUrl = null;
        if ($hyvorUser->picture_url !== null) {
            try {
                $media = $this->mediaService->uploadFromUrl($blog, $hyvorUser->picture_url);
                $pictureUrl = $this->permalinkService->getMediaPermalink($media, $blog);
            } catch (MediaUploadException) {
                // ignore: picture upload is best-effort
            }
        }

        $user = new User();
        $user->setBlog($blog);
        $user->setRole($role);
        $user->setStatus(UserStatus::ACTIVE);
        $user->setSlug($this->generateUniqueSlug($blog, [$hyvorUser->name, $hyvorUser->username, $hyvorUser->email]));
        $user->setHyvorUserId($hyvorUser->id);
        $user->setEmail($hyvorUser->email);
        $user->setWebsiteUrl($hyvorUser->website_url);
        $user->setPictureUrl($pictureUrl);
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);

        $this->em->persist($user);
        $this->em->flush();

        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        $this->createUserVariant(
            $user,
            $primaryLanguage,
            name: $hyvorUser->name,
            location: $hyvorUser->location,
            bio: $hyvorUser->bio,
        );

        $this->ed->dispatch(new UserCreatedEvent($user));

        return $user;
    }

    public function createGuestUser(Blog $blog, string $name, UserRole $role = UserRole::CONTRIBUTOR): User
    {
        $now = $this->now();

        $user = new User();
        $user->setBlog($blog);
        $user->setRole($role);
        $user->setStatus(UserStatus::ACTIVE);
        $user->setSlug($this->generateUniqueSlug($blog, [$name]));
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);

        $this->em->persist($user);
        $this->em->flush();

        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        $this->createUserVariant($user, $primaryLanguage, name: $name);

        $this->ed->dispatch(new UserCreatedEvent($user));

        return $user;
    }

    /**
     * @param array{
     *     hyvor_user_id?: ?int,
     *     role?: UserRole,
     *     status?: UserStatus,
     *     slug?: string,
     *     email?: ?string,
     *     website_url?: ?string,
     *     picture_url?: ?string,
     *     social_facebook?: ?string,
     *     social_twitter?: ?string,
     *     social_linkedin?: ?string,
     *     social_youtube?: ?string,
     *     social_tiktok?: ?string,
     *     social_instagram?: ?string,
     *     social_github?: ?string,
     * } $updates
     */
    public function updateUser(User $user, array $updates): User
    {
        $userOld = clone $user;

        if (isset($updates['hyvor_user_id'])) {
            $user->setHyvorUserId($updates['hyvor_user_id']);
        }
        if (isset($updates['role'])) {
            $user->setRole($updates['role']);
        }
        if (isset($updates['status'])) {
            $user->setStatus($updates['status']);
        }
        if (isset($updates['slug'])) {
            $user->setSlug($updates['slug']);
        }
        if (isset($updates['email'])) {
            $user->setEmail($updates['email']);
        }
        if (isset($updates['website_url'])) {
            $user->setWebsiteUrl($updates['website_url']);
        }
        if (isset($updates['picture_url'])) {
            $user->setPictureUrl($updates['picture_url']);
        }
        if (isset($updates['social_facebook'])) {
            $user->setSocialFacebook($updates['social_facebook']);
        }
        if (isset($updates['social_twitter'])) {
            $user->setSocialTwitter($updates['social_twitter']);
        }
        if (isset($updates['social_linkedin'])) {
            $user->setSocialLinkedin($updates['social_linkedin']);
        }
        if (isset($updates['social_youtube'])) {
            $user->setSocialYoutube($updates['social_youtube']);
        }
        if (isset($updates['social_tiktok'])) {
            $user->setSocialTiktok($updates['social_tiktok']);
        }
        if (isset($updates['social_instagram'])) {
            $user->setSocialInstagram($updates['social_instagram']);
        }
        if (isset($updates['social_github'])) {
            $user->setSocialGithub($updates['social_github']);
        }

        $user->setUpdatedAt($this->now());

        $this->em->flush();

        $this->ed->dispatch(new UserUpdatedEvent($user, $userOld));

        return $user;
    }

    public function getUserVariant(User $user, Language $language): ?UserVariant
    {
        /** @var UserVariant|null */
        return $this->em->getRepository(UserVariant::class)->findOneBy([
            'user' => $user,
            'language' => $language,
        ]);
    }

    public function createUserVariant(
        User $user,
        Language $language,
        ?string $name = null,
        ?string $location = null,
        ?string $bio = null,
    ): UserVariant {
        $variant = new UserVariant();
        $variant->setUser($user);
        $variant->setLanguage($language);
        $variant->setName($name);
        $variant->setLocation($location);
        $variant->setBio($bio);
        $variant->setUpdatedAt($this->now());

        $this->em->persist($variant);
        $this->em->flush();

        $user->getVariants()->add($variant);

        $this->ed->dispatch(new UserVariantCreatedEvent($variant));

        return $variant;
    }

    /**
     * @param array{name?: ?string, bio?: ?string, location?: ?string} $updates
     */
    public function updateUserVariant(UserVariant $variant, array $updates): UserVariant
    {
        $variantOld = clone $variant;

        if (isset($updates['name'])) {
            $variant->setName($updates['name']);
        }
        if (isset($updates['bio'])) {
            $variant->setBio($updates['bio']);
        }
        if (isset($updates['location'])) {
            $variant->setLocation($updates['location']);
        }

        $variant->setUpdatedAt($this->now());

        $this->em->flush();

        $this->ed->dispatch(new UserVariantUpdatedEvent($variant, $variantOld));

        return $variant;
    }

    public function deleteUserVariant(UserVariant $variant): void
    {
        $this->em->remove($variant);
        $this->em->flush();

        $this->ed->dispatch(new UserVariantDeletedEvent($variant));
    }

    /**
     * @param (string|null)[] $checks
     */
    private function generateUniqueSlug(Blog $blog, array $checks): string
    {
        $slugger = new AsciiSlugger();
        $i = 0;

        while (true) {
            $check = $checks[$i] ?? bin2hex(random_bytes(8));
            $slug = (string) $slugger->slug((string) $check)->lower();

            if ($slug !== '' && $this->getUserBySlug($blog, $slug) === null) {
                return $slug;
            }

            $i++;
        }
    }
}
