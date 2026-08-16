<?php

namespace App\Service\Integration\HyvorTalk;

use App\Entity\Blog;
use App\Entity\Enum\UserRole;
use App\Entity\HyvorTalkWebsite;
use App\Entity\User;
use App\Service\Blog\BlogService;
use App\Service\Language\LanguageService;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\CloudApi\Scope\TalkScope;
use Hyvor\Internal\Component\Component;
use Hyvor\Sdk\Exceptions\HyvorApiException;
use Hyvor\Sdk\Exceptions\NotFoundException;
use Hyvor\Sdk\Talk\TalkClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class HyvorTalkService
{
    use ClockAwareTrait;

    private const string DEFAULT_EMBED_CODE = <<<HTML
        <script async src="https://talk.hyvor.com/embed/embed.js" type="module"></script>
        <hyvor-talk-comments
            website-id="{website_id}"
            page-id="{{ _post.id }}"
            page-url="{{ _post.url }}"
        ></hyvor-talk-comments>

        <script type="module">
            // sync color mode with hyvor talk embed colors
            window.addEventListener('hb:colorModeChanged', setHtEmbedColors);
            function setHtEmbedColors() {
                const colorMode = window._hb.getColorMode();
                for (const frm of document.querySelectorAll('hyvor-talk-comments')) {
                    frm.setAttribute('colors', colorMode);
                }
            }
            setHtEmbedColors();
        </script>
        HTML;

    private const array REQUIRED_SCOPES = [
        // to create a new website when connecting
        TalkScope::ORG_WEBSITES_CREATE,

        // to list the website to choose from when connecting (currently not used)
        TalkScope::ORG_WEBSITES_READ,

        // read the website when disconnecting
        TalkScope::WEBSITE_READ,

        // delete the website when disconnecting
        TalkScope::WEBSITE_DELETE,

        // add/remove mods as blog users change
        TalkScope::MODS_WRITE,

        // add/remove domains
        TalkScope::DOMAINS_WRITE,
    ];

    public const array SYNCED_ROLES = [
        UserRole::ADMIN,
        UserRole::EDITOR,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private CloudApiService $cloudApiService,
        private PermalinkService $permalinkService,
        private BlogService $blogService,
        private LanguageService $languageService,
        private MessageBusInterface $bus,
        private LoggerInterface $logger
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
     * Maps a Hyvor Blogs user role to a Hyvor Talk moderator role.
     * Returns null if the role does not have access to the Hyvor Talk console.
     */
    public static function mapUserRole(UserRole $role): ?string
    {
        return match ($role) {
            UserRole::ADMIN => 'admin',
            UserRole::EDITOR => 'mod',
            default => null,
        };
    }

    /**
     * @throws HyvorApiException
     */
    public function connect(Blog $blog, User $connectingUser): HyvorTalkWebsite
    {
        $orgId = $blog->getOrganizationId();
        assert($orgId !== null);

        $language = $this->languageService->getPrimaryLanguage($blog);
        $variant = $this->blogService->getBlogVariant($blog, $language);
        assert($variant !== null);

        // guarded by ScopeRequired(Scope::INTEGRATIONS_MANAGE): only ADMIN/EDITOR can reach here
        $role = self::mapUserRole($connectingUser->getRole());
        assert($role !== null);

        $hyvorUserId = $connectingUser->getHyvorUserId();
        assert($hyvorUserId !== null);

        try {
            $website = $this->getClient($orgId)->org->websites->create([
                'name' => $variant->getName(),
                'domain' => $this->getDomainsOfBlog($blog)[0],
                'metadata' => [
                    'hyvor_blogs_integration' => 'true',
                    'hyvor_blogs_blog_id' => (string)$blog->getId(),
                ],
                'start_trial' => false,
            ]);

            // we add the current user as a mod in HT so they have access to it immediately
            // then, later, we will sync users in a job to give other users of the blog access to the website as well
            $this->addMod($orgId, $website->id, $hyvorUserId, $role);

        } catch (HyvorApiException $e) {
            $this->logger->error('Failed to connect blog to Hyvor Talk', [
                'blog_id' => $blog->getId(),
                'user_id' => $connectingUser->getId(),
                'exception' => $e,
            ]);
            throw $e;
        }

        $hyvorTalk = new HyvorTalkWebsite();
        $hyvorTalk->setBlog($blog);
        $hyvorTalk->setWebsiteId($website->id);
        // for now, we always create the website. Later we might have a way to connect an existing website
        $hyvorTalk->setCreatedByBlogs(true);
        $hyvorTalk->setCreatedAt($this->now());
        $hyvorTalk->setUpdatedAt($this->now());

        $this->em->persist($hyvorTalk);
        $this->em->flush();

        $this->bus->dispatch(new SyncBlogUsersToWebsiteMessage($blog->getId()));

        return $hyvorTalk;
    }

    /**
     * Disconnects a blog from Hyvor Talk.
     * Deletes the website if it was created by Hyvor Blogs.
     */
    public function disconnect(HyvorTalkWebsite $hyvorTalk): void
    {
        $this->em->remove($hyvorTalk);
        $this->em->flush();

        if ($hyvorTalk->isCreatedByBlogs()) {
            $orgId = $hyvorTalk->getBlog()->getOrganizationId();
            assert($orgId !== null);

            try {
                $this->getClient($orgId)
                    ->website($hyvorTalk->getWebsiteId())
                    ->delete();
            } catch (NotFoundException) {
                // website not found in Hyvor Talk; nothing to do
            }
        }
    }

    public function updateEmbedCode(HyvorTalkWebsite $hyvorTalk, ?string $embedCode): HyvorTalkWebsite
    {
        $hyvorTalk->setEmbedCode($embedCode);
        $hyvorTalk->setUpdatedAt($this->now());
        $this->em->flush();

        return $hyvorTalk;
    }

    /**
     * Adds a Hyvor user as a Hyvor Talk website moderator. Ignores the call if the
     * user is already added.
     */
    public function addMod(int $organizationId, int $websiteId, int $hyvorUserId, string $role): void
    {
        $this->getClient($organizationId)
            ->website($websiteId)
            ->mods
            ->create([
                'user_id' => $hyvorUserId,
                'role' => $role,
                'on_duplicate' => 'ignore',
            ]);
    }

    /**
     * Removes a Hyvor user from a Hyvor Talk website's moderators. Ignores the call if
     * the user is not found in Hyvor Talk.
     */
    public function removeMod(int $organizationId, int $websiteId, int $hyvorUserId): void
    {
        try {
            $this->getClient($organizationId)
                ->website($websiteId)
                ->mods
                ->delete(['user_id' => $hyvorUserId]);
        } catch (NotFoundException) {
            // user not found in Hyvor Talk; nothing to do
        }
    }

    public function addDomains(HyvorTalkWebsite $hyvorTalkWebsite): void
    {
        $blog = $hyvorTalkWebsite->getBlog();
        $orgId = $blog->getOrganizationId();
        assert($orgId !== null);

        $this->getClient($orgId)
            ->website($hyvorTalkWebsite->getWebsiteId())
            ->domains
            ->update([
                'domains' => $this->getDomainsOfBlog($blog),
                // we simply add new domains to HT, not caring about deleting old ones
                // HT ignores duplicates, so this is safe to call multiple times
                'operation' => 'add',
            ]);
    }

    public static function getDefaultEmbedCode(int $websiteId): string
    {
        return str_replace('{website_id}', (string) $websiteId, self::DEFAULT_EMBED_CODE);
    }

    public static function getEmbedCode(HyvorTalkWebsite $hyvorTalk): string
    {
        return $hyvorTalk->getEmbedCode() ?? self::getDefaultEmbedCode($hyvorTalk->getWebsiteId());
    }

    /**
     * @return non-empty-array<string>
     */
    private function getDomainsOfBlog(Blog $blog): array
    {
        $urls = [
            $this->permalinkService->getBlogUrl($blog)
        ];
        // add custom domains if any

        return array_map(fn($url) => parse_url($url, PHP_URL_HOST), $urls);
    }

}
