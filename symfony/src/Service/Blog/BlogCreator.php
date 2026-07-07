<?php

namespace App\Service\Blog;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\LanguageDirection;
use App\Entity\Enum\NavigationType;
use App\Entity\Enum\UserRole;
use App\Entity\Language;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\Language\LanguageService;
use App\Service\Navigation\NavigationService;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Route\RouteService;
use App\Service\Tag\TagService;
use App\Service\Theme\ThemeFilesService;
use App\Service\Theme\ThemeService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Resource\ResourceCreated;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BlogCreator
{
    use ClockAwareTrait;

    private const int RANDOM_POSTS_COUNT = 30;

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
        private PostContentService $postContentService,
        private BlogService $blogService,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {}

    public function create(
        ?AuthUser $authUser,
        ?int $organizationId,
        string $name,
        string $subdomain,
        BlogType $type = BlogType::DEFAULT,
        ?string $ip = null,
    ): Blog {
        /** @var Blog $blog */
        $blog =  $this->em->wrapInTransaction(function () use ($authUser, $organizationId, $name, $subdomain, $type, $ip) {
            $now = $this->now();

            $blog = new Blog();
            $blog->setCreatedAt($now);
            $blog->setUpdatedAt($now);
            $blog->setHyvorUserId($authUser?->id);
            $blog->setOrganizationId($organizationId);
            $blog->setIp($ip);
            $blog->setSubdomain($subdomain);
            $blog->setType($type);

            $this->em->persist($blog);

            $primaryLanguage = $this->fillLanguages($blog);
            $this->blogService->createBlogVariant($blog, $primaryLanguage, name: $name, flush: false);

            if ($type === BlogType::PREVIEW) {
                $meta = $blog->getMeta();
                $meta->cover_url = $this->getFeaturedImageUrl();
                $blog->setMeta($meta);
            }

            $primaryUser = $this->fillPrimaryUser($blog, $authUser);
            $this->fillAdditionalUsers($blog);
            $welcomeTag = $this->fillTags($blog);
            $this->fillPosts($blog, $primaryUser, $welcomeTag);
            $this->fillRoutes($blog);
            $this->fillNavigations($blog, $primaryUser);
            $this->fillTheme($blog);

            if ($organizationId !== null && $this->internalConfig->getDeployment()->isCloud()) {
                $this->comms->send(new ResourceCreated(Component::BLOGS, $organizationId));
            }

            return $blog;
        });

        return $blog;
    }

    /**
     * Fills primary language (and other languages for DEV/PREVIEW blogs) for the given blog.
     * @return Language The primary language of the blog.
     */
    private function fillLanguages(Blog $blog): Language
    {
        $primaryLanguage = $this->languageService->createLanguage($blog, 'en', 'English', isPrimary: true);

        if ($blog->getType()->isNonDefault()) {
            $this->languageService->createLanguage($blog, 'fr', 'French');
            $this->languageService->createLanguage($blog, 'ar', 'Arabic', LanguageDirection::RTL);
        }

        return $primaryLanguage;
    }

    private function fillPrimaryUser(Blog $blog, ?AuthUser $authUser): User
    {
        if ($blog->getType() === BlogType::PREVIEW || $authUser === null) {
            return $this->createRandomGuestUser($blog);
        }

        return $this->userService->createUserFromAuthUser($blog, $authUser, UserRole::ADMIN);
    }

    private function fillAdditionalUsers(Blog $blog): void
    {
        if ($blog->getType()->isNonDefault()) {
            for ($i = 0; $i < 5; $i++) {
                $this->createRandomGuestUser($blog);
            }
        }
    }

    private function createRandomGuestUser(Blog $blog): User
    {
        return $this->userService->createGuestUser(
            $blog,
            $this->getRandomUserName(),
            pictureUrl: $this->getUserImageUrl()
        );
    }

    /**
     * @return Tag welcome tag
     */
    private function fillTags(Blog $blog): Tag
    {
        $tag = $this->tagService->createTag($blog, 'Welcome');

        if ($blog->getType()->isNonDefault()) {
            for ($i = 0; $i < 5; $i++) {
                $this->tagService->createTag($blog, $this->getRandomTagName());
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
            );
        }
    }

    private function fillNavigations(Blog $blog, User $primaryUser): void
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

            $tags = $this->tagService->getTags($blog, 1);
            if ($tags !== []) {
                $navs[] = ['type' => NavigationType::HEADER, 'name' => 'Tag', 'url' => '/tag/' . $tags[0]->getSlug()];
            }
        }

        foreach ($navs as $nav) {
            $this->navigationService->createNavigation($blog, $nav['url'], $nav['type'], $nav['name']);
        }
    }

    /** @throws \App\Service\Theme\Exception\ThemeImportException */
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

    private function fillPosts(Blog $blog, User $primaryUser, Tag $welcomeTag): void
    {
        $language = $this->languageService->getPrimaryLanguage($blog);

        foreach (self::POST_DATA as $row) {
            $isPage = $row['type'] === 'page';
            $authors = !$isPage ? [$primaryUser] : [];

            $post = $this->postService->createPost(
                $blog,
                authors: $authors,
                isPage: $isPage,
                featuredImageUrl: $this->getFeaturedImageUrl(),
            );

            $content = (string) file_get_contents($this->projectDir . '/resources/posts/' . $row['file']);
            $json = $this->postContentService->getJsonFromHtml($content, $blog);

            $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);
            assert($variant !== null);

            $variant = $this->postService->updatePostVariant($variant, $blog, [
                'slug' => $row['slug'],
                'content' => $json,
                'title' => $row['title'],
                'description' => $row['description'] ?? '',
            ]);

            $this->postService->publishPostVariant($variant, $blog);

            if (!$isPage) {
                $this->postService->setPostTags($post, [$welcomeTag], flush: true);
            }
        }

        if ($blog->getType()->isNonDefault()) {
            $this->fillRandomPosts($blog);
        }
    }

    private function fillRandomPosts(Blog $blog): void
    {
        $languages = $this->languageService->getAllLanguages($blog);
        $tags = $this->tagService->getTags($blog, 100);
        $users = $this->userService->getUsers($blog, 100);

        for ($i = 0; $i < self::RANDOM_POSTS_COUNT; $i++) {
            $post = $this->postService->createPost(
                $blog,
                featuredImageUrl: $this->getFeaturedImageUrl(),
            );

            foreach ($languages as $language) {
                $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language)
                    ?? $this->postService->createPostVariant($post, $language, flush: false);

                $paragraphs = $this->getFakeParagraphs();
                $html = '<p>' . implode('</p><p>', $paragraphs) . '</p>';

                $variant = $this->postService->updatePostVariant($variant, $blog, [
                    'title' => $this->getRandomTitle(),
                    'content' => $this->postContentService->getJsonFromHtml($html, $blog),
                ]);

                $this->postService->publishPostVariant($variant, $blog);
            }

            if ($tags !== []) {
                $this->postService->setPostTags($post, $this->pickRandom($tags, 1, 3), flush: true);
            }

            if ($users !== []) {
                $this->postService->setPostAuthors($post, $this->pickRandom($users, 1, 3), flush: true);
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

    private function getRandomUserName(): string
    {
        $names = [
            'Alex Johnson',
            'Emily Smith',
            'Michael Brown',
            'Sophia Davis',
            'Daniel Wilson',
            'Olivia Martinez',
            'James Anderson',
            'Ava Taylor',
            'William Thomas',
            'Isabella Moore',
            'Benjamin Jackson',
        ];

        return $names[random_int(0, count($names) - 1)];
    }

    private function getRandomTagName(): string
    {
        $tags = [
            'Technology',
            'Lifestyle',
            'Travel',
            'Food',
            'Health',
            'Fashion',
            'Education',
            'Entertainment',
            'Sports',
            'Business',
        ];

        return $tags[random_int(0, count($tags) - 1)];
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

    private function getFeaturedImageUrl(): string
    {
        return self::IMAGE_URL_BASE . '/post-featured-images/' . random_int(1, 20) . '.webp';
    }

    private function getUserImageUrl(): string
    {
        return self::IMAGE_URL_BASE . '/author-images/' . random_int(1, 5) . '.webp';
    }
}
