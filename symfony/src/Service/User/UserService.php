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
use App\Service\Media\MediaException;
use App\Service\Route\PermalinkService;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserUpdatedEvent;
use App\Service\User\Event\UserVariantCreatedEvent;
use App\Service\User\Event\UserVariantDeletedEvent;
use App\Service\User\Event\UserVariantUpdatedEvent;
use App\Service\User\Exception\HyvorUserNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
use Hyvor\FilterQ\Keys;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class UserService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
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
    public function getAdmins(Blog $blog): array
    {
        /** @var User[] */
        return $this->userRepository->createQueryBuilder('u')
            ->where('u.blog = :blog')
            ->andWhere('u.role = :role')
            ->andWhere('u.status = :status')
            ->andWhere('u.email IS NOT NULL')
            ->setParameter('blog', $blog)
            ->setParameter('role', UserRole::ADMIN)
            ->setParameter('status', UserStatus::ACTIVE)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return User[]
     */
    public function getUsers(
        Blog $blog,
        int $limit,
        int $offset = 0,
        ?string $search = null,
    ): array {

        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->leftJoin('u.variants', 'uv')
            ->addSelect('uv')
            ->where('u.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('CASE WHEN u.role = :adminRole THEN 0 ELSE 1 END', 'ASC')
            ->addOrderBy('u.posts_count', 'DESC')
            ->setParameter('adminRole', UserRole::ADMIN)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($search) {
            $qb->andWhere('uv.name LIKE :search')
                ->setParameter('search', str_replace('%', '', $search) . '%');
        }

        /** @var User[] */
        return $qb->getQuery()
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
                ->keys(function (Keys $keys) {
                    $keys->add('id', 'u.id')->valueType('int');
                    $keys->add('slug', 'u.slug')->valueType('string')->operators('=,!=');
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
            ->andWhere('b.deleted_at IS NULL')
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
            $this->em->remove($user);
        });

        $this->ed->dispatch(new UserDeletedEvent($user));
    }

    /**
     * @throws HyvorUserNotFoundException
     */
    public function createUserFromAuthUser(
        Blog $blog,
        int|AuthUser $hyvorUserId,
        UserRole $role,
        bool $flush = true,
        ?Language $primaryLanguage = null, // to provide from outside
    ): User {
        if (is_int($hyvorUserId)) {
            $hyvorUser = $this->auth->fromId($hyvorUserId);

            if ($hyvorUser === null) {
                throw new HyvorUserNotFoundException();
            }
        } else {
            $hyvorUser = $hyvorUserId;
        }

        $now = $this->now();

        $pictureUrl = null;
        if ($hyvorUser->picture_url !== null) {
            try {
                $media = $this->mediaService->uploadFromUrl($blog, $hyvorUser->picture_url);
                $pictureUrl = $this->permalinkService->getMediaPermalink($media, $blog);
            } catch (MediaException) {
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
        $user->setCursorColor($this->generateCursorColor());
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);

        $language = $primaryLanguage ?? $this->languageService->getPrimaryLanguage($blog);
        $this->createUserVariant(
            $user,
            $language,
            name: $hyvorUser->name,
            location: $hyvorUser->location,
            bio: $hyvorUser->bio,
            flush: false
        );

        $this->em->persist($user);

        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch(new UserCreatedEvent($user));
        }

        $blog->getUsers()->add($user);

        return $user;
    }

    public function createGuestUser(
        Blog $blog,
        string $name,
        UserRole $role = UserRole::CONTRIBUTOR,
        ?string $pictureUrl = null,
        bool $flush = true,
        ?Language $primaryLanguage = null // to provide from outside
    ): User {
        $now = $this->now();

        $user = new User();
        $user->setBlog($blog);
        $user->setRole($role);
        $user->setStatus(UserStatus::ACTIVE);
        $user->setSlug($this->generateUniqueSlug($blog, [$name]));
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        $user->setPictureUrl($pictureUrl);
        $user->setCursorColor($this->generateCursorColor());

        $this->em->persist($user);

        $primaryLanguage = $primaryLanguage ?? $this->languageService->getPrimaryLanguage($blog);
        $this->createUserVariant($user, $primaryLanguage, name: $name, flush: false);

        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch(new UserCreatedEvent($user));
        }

        $blog->getUsers()->add($user);

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
        bool $flush = true
    ): UserVariant {
        $variant = new UserVariant();
        $variant->setUser($user);
        $variant->setLanguage($language);
        $variant->setName($name);
        $variant->setLocation($location);
        $variant->setBio($bio);
        $variant->setUpdatedAt($this->now());
        $user->getVariants()->add($variant);

        $this->em->persist($variant);

        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch(new UserVariantCreatedEvent($variant));
        }

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
            $slug = (string) $slugger->slug($check)->lower();

            if ($slug !== '' && $this->getUserBySlug($blog, $slug) === null) {
                return $slug;
            }

            $i++;
        }
    }

    /**
     * A random dark HSL color for this user's cursor.
     */
    public function generateCursorColor(): string
    {
        return sprintf('hsl(%d, 70%%, 35%%)', random_int(0, 359));
    }
}
