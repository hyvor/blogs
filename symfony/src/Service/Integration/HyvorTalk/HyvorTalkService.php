<?php

namespace App\Service\Integration\HyvorTalk;

use App\Entity\Blog;
use App\Entity\HyvorTalkWebsite;
use App\Entity\User;
use App\Service\Blog\BlogService;
use App\Service\Language\LanguageService;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\CloudApi\Scope\TalkScope;
use Hyvor\Internal\Component\Component;
use Hyvor\Sdk\Exceptions\HyvorApiException;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Sdk\Talk\TalkClient;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class HyvorTalkService
{

    use ClockAwareTrait;

    private const array REQUIRED_SCOPES = [
        TalkScope::ORG_WEBSITES_CREATE,
        TalkScope::WEBSITE_READ,
        TalkScope::DOMAINS_WRITE,
        TalkScope::MODS_WRITE,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private CloudApiService $cloudApiService,
        private PermalinkService $permalinkService,
        private BlogService $blogService,
        private LanguageService $languageService,
        private MessageBusInterface $bus,
    ) {}

    private function getClient(int $orgId): TalkClient
    {
        return $this->cloudApiService->getHyvorClientForOrganization(
            TalkClient::class,
            $orgId,
            Component::TALK,
            self::REQUIRED_SCOPES
        );
    }

    public function getHyvorTalkWebsiteOfBlog(Blog $blog): ?HyvorTalkWebsite
    {
        return $this->em->getRepository(HyvorTalkWebsite::class)->findOneBy(['blog' => $blog]);
    }

    /**
     * @throws HyvorApiException
     */
    public function connect(
        Blog $blog,
        AuthUser $user,
    ): HyvorTalkWebsite
    {
        $orgId = $blog->getOrganizationId();
        assert($orgId !== null);

        $variant = $this->blogService->getBlogVariant(
            $blog,
            $this->languageService->getPrimaryLanguage($blog)
        );

        $website = $this->getClient($orgId)->org->websites->create([
            'name' => $variant->getName(),
            'domain' => $this->getDomainsOfBlog($blog)[0],
            'metadata' => [
                'hyvor_blogs_integration' => 'true',
                'hyvor_blogs_blog_id' => (string) $blog->getId(),
            ],
            'start_trial' => false,
        ]);

        // we add the current user to the website in HT so he has access to it immediately
        // then, later, we will sync users in a job to give other users of the blog access to the website as well
        $this->addMod($orgId, $website->id, $user->id);

        $hyvorTalk = new HyvorTalkWebsite();
        $hyvorTalk->setBlog($blog);
        $hyvorTalk->setWebsiteId($website->id);
        // for now, we always create the newsletter. Later we might have a way to connect an existing newsletter
        $hyvorTalk->setCreatedByBlogs(true);
        $hyvorTalk->setCreatedAt($this->now());
        $hyvorTalk->setUpdatedAt($this->now());

        $this->em->persist($hyvorTalk);
        $this->em->flush();

        $this->bus->dispatch(new SyncBlogUsersToNewsletterMessage($blog->getId()));

        return $hyvorTalk;
    }

    /**
     * Adds a Hyvor user as a Hyvor Talk website moderator.
     * Ignores the call if the mod is already there.
     */
    public function addMod(int $organizationId, int $websiteId, User $blogUser): void
    {
        $this->getClient($organizationId)
            ->website($websiteId)
            ->moderators
            ->create([
                'user_id' => $blogUser->getHyvorUserId(),
                'on_duplicate' => 'ignore',
            ]);
    }

    /**
     * @return non-empty-array<string>
     */
    private function getDomainsOfBlog(Blog $blog): array
    {
        $urls = [
            $this->permalinkService->getBlogPermalink($blog)
        ];
        // add custom domains if any

        return array_map(fn($url) => parse_url($url, PHP_URL_HOST), $urls);
    }

}
