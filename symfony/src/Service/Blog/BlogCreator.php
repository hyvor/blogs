<?php

namespace App\Service\Blog;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\LanguageDirection;
use App\Entity\Enum\NavigationType;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserRole;
use App\Entity\Language;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\Blog\Event\BlogCreatedEvent;
use App\Service\Language\LanguageService;
use App\Service\Navigation\NavigationService;
use App\Service\Post\Content\PostSchema;
use App\Service\Post\PostService;
use App\Service\Route\RouteService;
use App\Service\Tag\TagService;
use App\Service\Theme\Exception\ThemeImportException;
use App\Service\Theme\ThemeFilesService;
use App\Service\Theme\ThemeService;
use App\Service\User\Exception\HyvorUserNotFoundException;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Resource\ResourceCreated;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BlogCreator
{
    use ClockAwareTrait;

    private const int RANDOM_POSTS_COUNT = 15;

    /**
     * TODO: self-hosted deployments should not depend on Cloudinary
     *       With the media domain update, change this so that filler images are served from it
     *       We can fetch those images from cloudinary in themes:sync
     */
    private const string IMAGE_URL_BASE = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images';

    /**
     * @var array<array{type: string, slug: string, title: string, file: string, description?: string}>
     */
    private const array POST_DATA = [
        // posts
        [
            'type' => 'post',
            'slug' => 'welcome',
            'title' => 'Welcome to Hyvor Blogs',
            'file' => 'post-welcome.html',
            'description' => 'A warm welcome to Hyvor Blogs. We have put together a few resources to help you get started with Hyvor Blogs',
        ],
        [
            'type' => 'post',
            'slug' => 'content-style',
            'title' => 'Content Style Guide',
            'file' => 'post-content-style.html',
            'description' => 'A post to show you all content styles available on Hyvor Blogs',
        ],

        // pages
        [
            'type' => 'page',
            'slug' => 'about',
            'title' => 'About',
            'file' => 'page-about.html',
        ],
        [
            'type' => 'page',
            'slug' => 'privacy',
            'title' => 'Privacy Policy',
            'file' => 'page-privacy.html',
        ],
        [
            'type' => 'page',
            'slug' => 'contact',
            'title' => 'Contact',
            'file' => 'page-contact.html',
        ],
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private CommsInterface $comms,
        private InternalConfig $internalConfig,
        private LanguageService $languageService,
        private UserService $userService,
        private TagService $tagService,
        private RouteService $routeService,
        private NavigationService $navigationService,
        private ThemeService $themeService,
        private ThemeFilesService $themeFilesService,
        private PostService $postService,
        private PostSchema $postSchema,
        private BlogService $blogService,
        private EventDispatcherInterface $ed,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {}

    /**
     * @return array{blog: Blog, primaryLanguage: Language, primaryUser: User}
     */
    public function create(
        ?AuthUser $authUser,
        ?int $organizationId,
        string $name,
        string $subdomain,
        BlogType $type = BlogType::DEFAULT,
        ?string $ip = null,
    ): array {

        /**
         * @var array{blog: Blog, primaryLanguage: Language, primaryUser: User} $data
         */
        $data =  $this->em->wrapInTransaction(function () use ($authUser, $organizationId, $name, $subdomain, $type, $ip) {
            $now = $this->now();

            $blog = new Blog();
            $blog->setCreatedAt($now);
            $blog->setUpdatedAt($now);
            $blog->setHyvorUserId($authUser?->id);
            $blog->setOrganizationId($organizationId);
            $blog->setIp($ip);
            $blog->setSubdomain($subdomain);
            $blog->setType($type);

            if ($type === BlogType::PREVIEW) {
                $meta = $blog->getMeta();
                $meta->cover_url = $this->getRandomFeaturedImageUrl();
                $blog->setMeta($meta);
            }

            $this->em->persist($blog);
            $this->em->flush();

            $primaryLanguage = $this->fillLanguages($blog);
            $this->blogService->createBlogVariant($blog, $primaryLanguage, name: $name, flush: false);

            $primaryUser = $this->fillPrimaryUser($blog, $authUser, $primaryLanguage);
            $this->fillAdditionalUsers($blog, $primaryLanguage);
            $welcomeTag = $this->fillTags($blog, $primaryLanguage);
            $this->fillPosts($blog, $primaryUser, $welcomeTag, $primaryLanguage);
            $this->fillRoutes($blog);
            $this->fillNavigations($blog, $primaryUser, $primaryLanguage);
            $this->fillTheme($blog);

            if ($organizationId !== null && $this->internalConfig->getDeployment()->isCloud()) {
                $this->comms->send(new ResourceCreated(Component::BLOGS, $organizationId));
            }

            $this->ed->dispatch(new BlogCreatedEvent($blog));

            return [
                'blog' => $blog,
                'primaryLanguage' => $primaryLanguage,
                'primaryUser' => $primaryUser,
            ];
        });

        return $data;
    }

    /**
     * Fills primary language (and other languages for DEV/PREVIEW blogs) for the given blog.
     * @return Language The primary language of the blog.
     */
    private function fillLanguages(Blog $blog): Language
    {
        $primaryLanguage = $this->languageService->createLanguage(
            $blog,
            'en',
            'English',
            isPrimary: true,
            flush: false
        );

        if ($blog->getType()->isNonDefault()) {
            $this->languageService->createLanguage($blog, 'fr', 'French', flush: false);
            $this->languageService->createLanguage($blog, 'ar', 'Arabic', LanguageDirection::RTL, flush: false);
        }

        return $primaryLanguage;
    }

    /**
     * @throws HyvorUserNotFoundException
     */
    private function fillPrimaryUser(Blog $blog, ?AuthUser $authUser, Language $primaryLanguage): User
    {
        if ($blog->getType() === BlogType::PREVIEW || $authUser === null) {
            return $this->userService->createGuestUser(
                $blog,
                'John Doe',
                pictureUrl: $this->getRandomUserImageUrl(),
                flush: false,
                primaryLanguage: $primaryLanguage
            );
        }

        return $this->userService->createUserFromAuthUser(
            $blog,
            $authUser,
            UserRole::ADMIN,
            flush: false,
            primaryLanguage: $primaryLanguage,
        );
    }

    private function fillAdditionalUsers(Blog $blog, Language $primaryLanguage): void
    {
        if ($blog->getType()->isNonDefault()) {
            $names = [
                'Alex Johnson',
                'Emily Smith',
                'Michael Brown',
                'Sophia Davis',
                'Daniel Wilson',
            ];

            foreach ($names as $name) {
                $this->userService->createGuestUser(
                    $blog,
                    $name,
                    pictureUrl: $this->getRandomUserImageUrl(),
                    flush: false,
                    primaryLanguage: $primaryLanguage
                );
            }
        }
    }

    /**
     * @return Tag welcome tag
     */
    private function fillTags(Blog $blog, Language $primaryLanguage): Tag
    {
        $tag = $this->tagService->createTag($blog, 'Welcome', flush: false, primaryLanguage: $primaryLanguage);

        if ($blog->getType()->isNonDefault()) {

            $tagNames = [
                'Technology',
                'Lifestyle',
                'Travel',
                'Food',
                'Health',
            ];

            foreach ($tagNames as $tagName) {
                $this->tagService->createTag($blog, $tagName, flush: false, primaryLanguage: $primaryLanguage);
            }
        }

        return $tag;
    }

    private function fillRoutes(Blog $blog): void
    {
        foreach (RouteService::ROUTES as $route) {
            $this->routeService->createRoute(
                $blog,
                $route['name'],
                $route['match'],
                $route['template'],
                $route['posts_filter'] ?? null,
                null,
                flush: false
            );
        }
    }

    private function fillNavigations(Blog $blog, User $primaryUser, Language $primaryLanguage): void
    {
        $isExtended = $blog->getType()->isNonDefault();

        // for DEV/PREVIEW blogs, "About" and "Contact" go to the footer instead of the header
        $aboutContactType = $isExtended ? NavigationType::FOOTER : NavigationType::HEADER;

        /** @var array<array{type: NavigationType, name: string, url: string}> $navs */
        $navs = [
            ['type' => $aboutContactType, 'name' => 'About', 'url' => '/about'],
            ['type' => $aboutContactType, 'name' => 'Contact', 'url' => '/contact'],
            ['type' => NavigationType::FOOTER, 'name' => 'Privacy Policy', 'url' => '/privacy'],
        ];

        if ($isExtended) {
            $navs[] = ['type' => NavigationType::HEADER, 'name' => 'Content Style', 'url' => '/content-style'];

            $navs[] = [
                'type' => NavigationType::HEADER,
                'name' => 'Author',
                'url' => '/author/' . $primaryUser->getSlug()
            ];

            $tags = $blog->getTags()->toArray();
            if ($tags !== []) {
                $navs[] = ['type' => NavigationType::HEADER, 'name' => 'Tag', 'url' => '/tag/' . $tags[0]->getSlug()];
            }
        }

        foreach ($navs as $nav) {
            $this->navigationService->createNavigation($blog, $nav['url'], $nav['type'], $nav['name'], flush: false, primaryLanguage: $primaryLanguage);
        }
    }

    /** @throws ThemeImportException */
    private function fillTheme(Blog $blog): void
    {
        if ($blog->getType() === BlogType::PREVIEW) {
            return;
        }

        $themeName = $blog->getType() === BlogType::DEV ? 'blank' : 'hello';

        $theme = $this->themeService->getThemeByName($themeName);
        if ($theme === null) {
            return;
        }

        $version = $this->themeService->getThemeLatestVersion($theme);
        if ($version === null) {
            throw new UnprocessableEntityHttpException('Theme version not found');
        }

        $this->themeFilesService->updateFilesFromThemeVersion($blog, $version);
    }

    private function fillPosts(Blog $blog, User $primaryUser, Tag $welcomeTag, Language $primaryLanguage): void
    {
        foreach (self::POST_DATA as $row) {
            $isPage = $row['type'] === 'page';
            $authors = !$isPage ? [$primaryUser] : [];

            $post = $this->postService->createPost(
                $blog,
                authors: $authors,
                isPage: $isPage,
                featuredImageUrl: $this->getRandomFeaturedImageUrl(),
                createVariant: false,
                flush: false,
            );

            $content = (string) file_get_contents($this->projectDir . '/resources/posts/' . $row['file']);
            $json = $this->postSchema->documentFromHtml($content)->toJson();

            $this->postService->createPostVariant(
                $post,
                $primaryLanguage,
                flush: false,
                status: PostVariantStatus::PUBLISHED,
                slug: $row['slug'],
                title: $row['title'],
                description: $row['description'] ?? '',
                content: $json
            );

            if (!$isPage) {
                $this->postService->setPostTags($post, [$welcomeTag], flush: false);
            }
        }

        if ($blog->getType()->isNonDefault()) {
            $this->fillRandomPosts($blog);
        }
    }

    private function fillRandomPosts(Blog $blog): void
    {
        $languages = $blog->getLanguages();
        $tags = $blog->getTags();
        $users = $blog->getUsers();

        for ($i = 0; $i < self::RANDOM_POSTS_COUNT; $i++) {
            $post = $this->postService->createPost(
                $blog,
                featuredImageUrl: $this->getRandomFeaturedImageUrl(),
                createVariant: false,
                flush: false
            );

            foreach ($languages as $language) {
                $paragraphs = $this->getFakeParagraphs();
                $html = '<p>' . implode('</p><p>', $paragraphs) . '</p>';

                $this->postService->createPostVariant(
                    $post,
                    $language,
                    flush: false,
                    status: PostVariantStatus::PUBLISHED,
                    slug: 'post-' . bin2hex(random_bytes(4)) . '-' . $i,
                    title: $this->getRandomTitle(),
                    content: $this->postSchema->documentFromHtml($html)->toJson(),
                );
            }

            if ($tags->count() > 0) {
                $this->postService->setPostTags($post, $this->pickRandom($tags->toArray(), 1, 3), flush: false);
            }

            if ($users->count() > 0) {
                $this->postService->setPostAuthors($post, $this->pickRandom($users->toArray(), 1, 3), flush: false);
            }
        }
    }

    /**
     * @param Tag[]|User[] $items
     * @return ($items is Tag[] ? Tag[] : User[])
     */
    private function pickRandom(array $items, int $min, int $max): array
    {
        $count = min(count($items), random_int($min, $max));
        $shuffled = $items;
        shuffle($shuffled);
        return array_slice($shuffled, 0, $count);
    }

    /**
     * @return string[]
     */
    private function getFakeParagraphs(): array
    {
        return [
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.',
            'Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. Proin porttitor, orci nec nonummy molestie',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.',
            'Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. Proin porttitor, orci nec nonummy molestie',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.',
        ];
    }

    private function getRandomTitle(): string
    {
        $titles = [
            'The Future of Technology: Trends to Watch',
            '10 Tips for a Healthier Lifestyle',
            'Exploring the World: Top Travel Destinations',
            'Delicious Recipes for Every Occasion',
            'The Importance of Mental Health Awareness',
            'Fashion Forward: Latest Trends and Styles',
            'Education in the Digital Age: Challenges and Opportunities',
            'Entertainment Industry Insights: Behind the Scenes',
            'Sports Highlights: Memorable Moments and Achievements',
            'Business Strategies for Success in a Competitive Market',
        ];

        return $titles[random_int(0, count($titles) - 1)];
    }

    private function getRandomFeaturedImageUrl(): string
    {
        return self::IMAGE_URL_BASE . '/post-featured-images/' . random_int(1, 20) . '.webp';
    }

    private function getRandomUserImageUrl(): string
    {
        return self::IMAGE_URL_BASE . '/author-images/' . random_int(1, 5) . '.webp';
    }
}
