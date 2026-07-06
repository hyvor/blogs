<?php

namespace App\Service\Blog;

use App\Entity\Blog;
use App\Entity\BlogVariant;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\LanguageDirection;
use App\Entity\Enum\NavigationType;
use App\Entity\Enum\UserRole;
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
use Faker\Factory;
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
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {}

    public function create(
        ?int $hyvorUserId,
        ?int $organizationId,
        string $name,
        string $subdomain,
        BlogType $type = BlogType::DEFAULT,
        ?string $ip = null,
    ): Blog {
        /** @var Blog */
        return $this->em->wrapInTransaction(function () use ($hyvorUserId, $organizationId, $name, $subdomain, $type, $ip) {
            $now = $this->now();

            $blog = new Blog();
            $blog->setHyvorUserId($hyvorUserId);
            $blog->setOrganizationId($organizationId);
            $blog->setIp($ip);
            $blog->setSubdomain($subdomain);
            $blog->setType($type);
            $blog->setTrialEndsAt($now); // TODO:
            $blog->setCreatedAt($now);
            $blog->setUpdatedAt($now);

            $this->em->persist($blog);
            $this->em->flush();

            if ($organizationId !== null && $this->internalConfig->getDeployment()->isCloud()) {
                $this->comms->send(new ResourceCreated(Component::BLOGS, $organizationId));
            }

            $this->fillLanguages($blog);

            if ($type === BlogType::PREVIEW) {
                $meta = $blog->getMeta();
                $meta->cover_url = $this->getFeaturedImageUrl();
                $blog->setMeta($meta);
            }

            /**
             * Creating the variant is important because all functions are designed assuming
             * the primary variant exists. Other fillers can be risky (theme copying, for example),
             * so we create the languages and the variant first, then run the other fillers.
             */
            $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
            $variant = new BlogVariant();
            $variant->setBlog($blog);
            $variant->setLanguage($primaryLanguage);
            $variant->setName($name);
            $this->em->persist($variant);
            $this->em->flush();
            $blog->getVariants()->add($variant);

            $this->fillUsers($blog);
            $this->fillTags($blog);
            $this->fillPosts($blog);
            $this->fillRoutes($blog);
            $this->fillNavigations($blog);
            $this->fillTheme($blog);

            return $blog;
        });
    }

    private function fillLanguages(Blog $blog): void
    {
        $this->languageService->createLanguage($blog, 'en', 'English', isPrimary: true);

        if ($blog->getType() === BlogType::DEV || $blog->getType() === BlogType::PREVIEW) {
            $this->languageService->createLanguage($blog, 'fr', 'French');
            $this->languageService->createLanguage($blog, 'ar', 'Arabic', LanguageDirection::RTL);
        }
    }

    private function fillUsers(Blog $blog): void
    {
        if ($blog->getType() === BlogType::TEMP) {
            $user = $this->userService->createGuestUser($blog, 'Temporary User', UserRole::ADMIN);
            $this->userService->updateUser($user, ['picture_url' => $this->getUserImageUrl()]);
        } elseif ($blog->getType() !== BlogType::PREVIEW) {
            $hyvorUserId = $blog->getHyvorUserId();
            assert($hyvorUserId !== null);
            $this->userService->createUserFromHyvorUser($blog, $hyvorUserId, UserRole::ADMIN);
        }

        if ($blog->getType() === BlogType::DEV || $blog->getType() === BlogType::PREVIEW) {
            $faker = Factory::create();

            for ($i = 0; $i < 5; $i++) {
                $user = $this->userService->createGuestUser($blog, $faker->name());
                $this->userService->updateUser($user, ['picture_url' => $this->getUserImageUrl()]);
            }
        }
    }

    private function fillTags(Blog $blog): void
    {
        $this->tagService->createTag($blog, 'Welcome');

        if ($blog->getType() === BlogType::DEV || $blog->getType() === BlogType::PREVIEW) {
            $faker = Factory::create();

            for ($i = 0; $i < 5; $i++) {
                $this->tagService->createTag($blog, $faker->word());
            }
        }
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

    private function fillNavigations(Blog $blog): void
    {
        $isExtended = $blog->getType() === BlogType::DEV || $blog->getType() === BlogType::PREVIEW;

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

            $owner = $this->userService->getOwner($blog);
            if ($owner !== null) {
                $navs[] = ['type' => NavigationType::HEADER, 'name' => 'Author', 'url' => '/author/' . $owner->getSlug()];
            }

            $tags = $this->tagService->getTags($blog, 1);
            if ($tags !== []) {
                $navs[] = ['type' => NavigationType::HEADER, 'name' => 'Tag', 'url' => '/tag/' . $tags[0]->getSlug()];
            }
        }

        foreach ($navs as $nav) {
            $this->navigationService->createNavigation($blog, $nav['url'], $nav['type'], $nav['name']);
        }
    }

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

    /**
     * Known issue: PostContentService::getJsonFromHtml() below sanitizes by default, and
     * hyvor/phrosemirror's Sanitizer currently throws
     * `DeepCopy\Exception\CloneException: The class "ReflectionClass" is not cloneable`
     * when given a real, Doctrine-managed Blog (its proxies/relations carry ReflectionClass
     * state that myclabs/deep-copy can't clone). This is a pre-existing bug in that vendor
     * package, unrelated to blog creation itself — it needs an upstream fix.
     */
    private function fillPosts(Blog $blog): void
    {
        $language = $this->languageService->getPrimaryLanguage($blog);
        $owner = $this->userService->getOwner($blog);
        $tags = $this->tagService->getTags($blog, 1);
        $tag = $tags[0] ?? null;

        foreach (self::POST_DATA as $row) {
            $isPage = $row['type'] === 'page';
            $authors = !$isPage && $owner !== null ? [$owner] : [];

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

            if (!$isPage && $tag instanceof Tag) {
                $this->postService->setPostTags($post, [$tag], flush: true);
            }
        }

        if ($blog->getType() === BlogType::DEV || $blog->getType() === BlogType::PREVIEW) {
            $this->fillRandomPosts($blog);
        }
    }

    private function fillRandomPosts(Blog $blog): void
    {
        $faker = Factory::create();

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

                /** @var string[] $paragraphs */
                $paragraphs = $faker->paragraphs(5, false);
                $html = '<p>' . implode('</p><p>', $paragraphs) . '</p>';

                $variant = $this->postService->updatePostVariant($variant, $blog, [
                    'title' => $faker->sentence(),
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

    private function getFeaturedImageUrl(): string
    {
        return self::IMAGE_URL_BASE . '/post-featured-images/' . random_int(1, 20) . '.webp';
    }

    private function getUserImageUrl(): string
    {
        return self::IMAGE_URL_BASE . '/author-images/' . random_int(1, 5) . '.webp';
    }
}
