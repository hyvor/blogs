<?php

namespace App\Service\Integration\HyvorPost;

use App\Entity\Blog;
use App\Entity\Enum\UserRole;
use App\Entity\HyvorPost;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\Component\Component;
use Hyvor\Sdk\Exceptions\NotFoundException;
use Hyvor\Sdk\Post\PostClient;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class HyvorPostService
{
    use ClockAwareTrait;

    private const string DEFAULT_EMBED_CODE = <<<HTML
        <script src="https://post.hyvor.com/form/form.js" type="module" async></script>
        <hyvor-post-form newsletter-id="{newsletter_id}"></hyvor-post-form>
        HTML;

    private const array REQUIRED_SCOPES = [
        // to create a new newsletter when connecting
        PostScope::ORG_NEWSLETTERS_CREATE,

        // to list the newsletter to choose from when connecting (currently not used)
        PostScope::ORG_NEWSLETTERS_READ,

        // delete the newsletter when disconnecting
        PostScope::NEWSLETTER_DELETE,

        // add, remove users automatically as they are added/removed in the blog
        PostScope::USERS_READ,
        PostScope::USERS_WRITE,
    ];

    public const array SYNCED_ROLES = [
        UserRole::ADMIN,
        UserRole::EDITOR,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private CloudApiService $cloudApiService,
        private MessageBusInterface $bus
    ) {}

    private function getClient(int $orgId): PostClient
    {
        $hyvorClient = $this->cloudApiService->getHyvorClientForOrganization(
            $orgId,
            Component::POST,
            self::REQUIRED_SCOPES
        );

        return $hyvorClient->post;
    }

    public function getHyvorPostOfBlog(Blog $blog): ?HyvorPost
    {
        return $this->em->getRepository(HyvorPost::class)->findOneBy(['blog' => $blog]);
    }

    public function connect(Blog $blog, string $name, string $subdomain, AuthUser $user): HyvorPost
    {
        $orgId = $blog->getOrganizationId();
        assert($orgId !== null);

        $newsletter = $this->getClient($orgId)->newsletters->create([
            'name' => $name,
            'subdomain' => $subdomain,
            'autogenerate_subdomain_on_duplicate' => true,
            'metadata' => [
                'hyvor_blogs_integration' => 'true',
                'hyvor_blogs_blog_id' => (string) $blog->getId(),
            ],
        ]);

        // we add the current user to the newsletter so he has access to it immediately
        // then, later, we will sync users in a job to give other users of the blog access to the newsletter as well
        $this->addUser($orgId, $newsletter->id, $user->id);

        $hyvorPost = new HyvorPost();
        $hyvorPost->setBlog($blog);
        $hyvorPost->setNewsletterId($newsletter->id);
        // for now, we always create the newsletter. Later we might have a way to connect an existing newsletter
        $hyvorPost->setCreatedByBlogs(true);
        $hyvorPost->setCreatedAt($this->now());
        $hyvorPost->setUpdatedAt($this->now());

        $this->em->persist($hyvorPost);
        $this->em->flush();

        $this->bus->dispatch(new SyncBlogUsersToNewsletterMessage($blog->getId()));

        return $hyvorPost;
    }

    /**
     * Disconnects a blog from Hyvor Post.
     * Deletes the newsletter if it was created by Hyvor Blogs.
     */
    public function disconnect(HyvorPost $hyvorPost): void
    {
        $this->em->remove($hyvorPost);
        $this->em->flush();

        if ($hyvorPost->isCreatedByBlogs()) {
            $orgId = $hyvorPost->getBlog()->getOrganizationId();
            assert($orgId !== null);

            try {
                $this->getClient($orgId)
                    ->newsletter($hyvorPost->getNewsletterId())
                    ->delete();
            } catch (NotFoundException) {
                // newsletter not found in Hyvor Post; nothing to do
            }
        }
    }

    public function updateEmbedCode(HyvorPost $hyvorPost, ?string $embedCode): HyvorPost
    {
        $hyvorPost->setEmbedCode($embedCode);
        $hyvorPost->setUpdatedAt($this->now());
        $this->em->flush();

        return $hyvorPost;
    }

    /**
     * Adds a Hyvor user as a Hyvor Post newsletter user. Ignores the call if the
     * user is already added.
     */
    public function addUser(int $organizationId, int $newsletterId, int $hyvorUserId): void
    {
        $this->getClient($organizationId)
            ->newsletter($newsletterId)
            ->users
            ->create([
                'user_id' => $hyvorUserId,
                'on_duplicate' => 'ignore',
            ]);
    }

    /**
     * Removes a Hyvor user from a Hyvor Post newsletter. Ignores the call if the
     * user is not found in Hyvor Post.
     */
    public function removeUser(HyvorPost $hyvorPost, int $hyvorUserId): void
    {
        $orgId = $hyvorPost->getBlog()->getOrganizationId();
        assert($orgId !== null);

        try {
            $this->getClient($orgId)
                ->newsletter($hyvorPost->getNewsletterId())
                ->users
                ->delete(['user_id' => $hyvorUserId]);
        } catch (NotFoundException) {
            // user not found in Hyvor Post; nothing to do
        }
    }

    public static function getDefaultEmbedCode(int $newsletterId): string
    {
        return str_replace('{newsletter_id}', (string) $newsletterId, self::DEFAULT_EMBED_CODE);
    }

    public static function getEmbedCode(HyvorPost $hyvorPost): string
    {
        return $hyvorPost->getEmbedCode() ?? self::getDefaultEmbedCode($hyvorPost->getNewsletterId());
    }
}
