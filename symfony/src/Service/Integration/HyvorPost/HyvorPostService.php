<?php

namespace App\Service\Integration\HyvorPost;

use App\Entity\Blog;
use App\Entity\HyvorPost;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\Component\Component;
use Hyvor\Sdk\Post\PostClient;
use Symfony\Component\Clock\ClockAwareTrait;

class HyvorPostService
{
    use ClockAwareTrait;

    public const string DEFAULT_EMBED_CODE = <<<HTML
        <script src="https://post.hyvor.com/form/form.js" type="module" async></script>
        <hyvor-post-form newsletter-id="{newsletter-id}"></hyvor-post-form>
        HTML;

    private const array REQUIRED_SCOPES = [
        // to create a new newsletter when connecting
        PostScope::ORG_NEWSLETTERS_CREATE,

        // to list the newsletter to choose from when connecting (currently not used)
        PostScope::ORG_NEWSLETTERS_READ,

        // add, remove users automatically as they are added/removed in the blog
        PostScope::USERS_READ,
        PostScope::USERS_WRITE,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private CloudApiService $cloudApiService,
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

    public function connect(Blog $blog, string $name, string $subdomain): HyvorPost
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

        $hyvorPost = new HyvorPost();
        $hyvorPost->setBlog($blog);
        $hyvorPost->setNewsletterId((int) $newsletter->id);
        $hyvorPost->setCreatedByBlogs(true);
        $hyvorPost->setCreatedAt($this->now());
        $hyvorPost->setUpdatedAt($this->now());

        $this->em->persist($hyvorPost);
        $this->em->flush();

        return $hyvorPost;
    }

    /**
     * Disconnects a blog from Hyvor Post.
     *
     * Note: this only removes the local integration row. It does not delete the
     * newsletter on Hyvor Post itself (even if it was created_by_blogs), since the
     * SDK does not yet support deleting a newsletter.
     */
    public function disconnect(HyvorPost $hyvorPost): void
    {
        $this->em->remove($hyvorPost);
        $this->em->flush();
    }

    public function updateEmbedCode(HyvorPost $hyvorPost, ?string $embedCode): HyvorPost
    {
        $hyvorPost->setEmbedCode($embedCode);
        $hyvorPost->setUpdatedAt($this->now());
        $this->em->flush();

        return $hyvorPost;
    }
}
