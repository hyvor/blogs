<?php

namespace App\Service\Delivery\TemplateRenderer;

use App\Api\Data\Factory\AuthorObjectFactory;
use App\Api\Data\Factory\BlogObjectFactory;
use App\Api\Data\Factory\PostObjectFactory;
use App\Api\Data\Factory\TagObjectFactory;
use App\Api\Data\Object\LanguageObject;
use App\Api\Data\Object\MetaObject;
use App\Api\Data\Object\PaginationObject;
use App\Api\Data\Object\RouteObject;
use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\Route;
use App\Entity\Tag;
use App\Entity\ThemeFile;
use App\Entity\User;
use App\Service\AppConfig;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Route\PermalinkService;
use App\Service\Tag\TagService;
use App\Service\Theme\Exception\ThemeConfigParsingException;
use App\Service\Theme\ThemeConfigService;
use App\Service\Theme\ThemeFilesService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Error\Error;

class TemplateRendererService
{
    public function __construct(
        private PermalinkService $permalinkService,
        private ThemeFilesService $themeFilesService,
        private ThemeConfigService $themeConfigService,
        private TwigRendererService $twigRendererService,
        private PostService $postService,
        private BlogObjectFactory $blogObjectFactory,
        private PostObjectFactory $postObjectFactory,
        private TagObjectFactory $tagObjectFactory,
        private AuthorObjectFactory $authorObjectFactory,
        private PostContentService $postContentService,
        private AppConfig $appConfig,
        private TagService $tagService,
        private UserService $userService,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private EventDispatcherInterface $ed,
    ) {}

    /**
     * @throws TemplateRenderingPageNotFoundException when the route / model is not found (404)
     * @throws TemplateRenderingException when the template rendering fails (500)
     */
    public function renderForRoute(
        Blog $blog,
        Language $language,
        Route $route,
        MatchedRoute $matchedRoute,
        // used by PreviewProcessor to skip slug-based lookup
        Post|null $presetModel = null,
    ): string {
        $model = $presetModel ?? $this->getModel($blog, $language, $route, $matchedRoute);
        if ($model === false) {
            throw new TemplateRenderingPageNotFoundException();
        }

        $templateFiles = $this->themeFilesService->getFilesInFolder($blog, ThemeFileFolder::TEMPLATES);
        $templateName = $this->getTemplateNameFromRoute($route, array_map(fn($f) => $f->getName(), $templateFiles));
        $vars = $this->getVariablesForRoute($blog, $language, $route, $matchedRoute, $model, $templateName);

        return $this->renderBlogTemplate($blog, $templateName, $vars);
    }

    /**
     * @throws TemplateRenderingException
     */
    public function renderWithoutRoute(Blog $blog, Language $language, string $templateName, string $path): string
    {
        $vars = $this->getDefaultVariables($blog, $language);

        $url = $this->permalinkService->getBlogUrlWithPath($blog, $path);
        $meta = new MetaObject(null, null, null, $url, $url);
        $vars['_meta'] = $meta;

        return $this->renderBlogTemplate($blog, $templateName, $vars);
    }

    /**
     * @param ThemeFile[] $templateFiles
     * @throws TemplateRenderingException
     */
    private function renderBlogTemplate(
        Blog $blog,
        string $template,
        array $vars,

        // send if previously loaded
        ?array $templateFiles = null
    ): string
    {
        $templateFiles ??= $this->themeFilesService->getFilesInFolder($blog, ThemeFileFolder::TEMPLATES);
        $loaderArray = [];

        foreach ($templateFiles as $file) {
            $loaderArray[$file->getName()] = $file->getContent() ?? '';
        }

        if (!array_key_exists($template, $loaderArray)) {
            throw new TemplateRenderingException("Template file '$template' not found in blog templates.");
        }

        /**
         * JSON encoding + decoding is to make sure only data from objects are sent
         * and the developer does not have access to any mistakenly added PHP methods
         * @var array<string, mixed> $vars
         */
        $vars = json_decode((string)json_encode($vars), true);

        // allow services to modify the variables before rendering
        $event = new TemplateRenderingEvent($blog, $vars);
        $this->ed->dispatch($event);

        try {
            return $this->twigRendererService->renderFromFiles(
                $loaderArray,
                $event->getVariables(),
                $template
            );
        } catch (Error $e) {
            throw new TemplateRenderingException("Unable to render template '$template'. Twig error: " . $e->getMessage());
        }
    }


    /** @param string[] $availableFiles */
    private function getTemplateNameFromRoute(Route $route, array $availableFiles): string
    {
        $checkFiles = explode(',', $route->getTemplate());
        foreach ($checkFiles as $file) {
            $file = trim($file) . '.twig';
            if (in_array($file, $availableFiles, true)) {
                return $file;
            }
        }
        return 'index.twig';
    }

    /**
     * Returns Post|Tag|User|null (no model needed) or false (404)
     * @return Post|Tag|User|null|false
     */
    private function getModel(Blog $blog, Language $language, Route $route, MatchedRoute $matchedRoute): Post|Tag|User|null|false
    {
        $slug = $matchedRoute->param('slug');
        $routeName = $route->getName();

        if ($routeName === 'tag') {
            if ($slug === null) return false;
            $tag = $this->tagService->getTagBySlug($blog, $slug);
            if ($tag === null) return false;
            if ($tag->isPrivate()) return false; // private pages do not have public pages
            return $tag;
        }

        if ($routeName === 'author') {
            if ($slug === null) return false;
            $user = $this->userService->getUserBySlug($blog, $slug);
            if ($user === null) return false;
            return $user;
        }

        if ($routeName === 'post' || $routeName === 'page') {
            if ($slug === null) return false;

            $variant = $this->postService->getPostVariantByLanguageAndSlug($language, $slug);
            if ($variant === null) return false;

            $post = $variant->getPost();

            if ($routeName === 'page' && !$post->isPage()) return false;
            if ($routeName === 'post' && $post->isPage()) return false;
            if ($post->getBlog()->getId() !== $blog->getId()) return false; // just in case

            if (!$this->permalinkService->validatePostPermalinkParams($post, $matchedRoute->params)) {
                return false;
            }

            if ($variant->getStatus() !== PostVariantStatus::PUBLISHED) {
                return false;
            }

            return $post;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     * @throws TemplateRenderingPageNotFoundException
     * @throws TemplateRenderingException
     */
    private function getVariablesForRoute(
        Blog $blog,
        Language $language,
        Route $route,
        MatchedRoute $matchedRoute,
        Post|Tag|User|null $model,
        string $templateName,
    ): array {
        $vars  = $this->getDefaultVariables($blog, $language);
        $vars['_route'] = new RouteObject($route, $matchedRoute, $templateName);

        $vars += $this->getRouteVariables($blog, $language, $matchedRoute, $model);
        $vars += $this->getPostFilterVariables($blog, $language, $matchedRoute);

        return $vars;
    }

    /**
     * @throws TemplateRenderingException
     */
    private function getDefaultVariables(Blog $blog, Language $language): array
    {
        try {
            $config = $this->themeConfigService->getConfig($blog);
        } catch (ThemeConfigParsingException $e) {
            throw new TemplateRenderingException($e->getMessage(), previous: $e);
        }
        $blogObject = $this->blogObjectFactory->create($blog, $language);

        return [
            // internal
            '__domain' => $this->appConfig->getDomainApp(),

            // vars for all routes
            '_blog' => $blogObject,
            '_config' => $config,
            '_lang' => new LanguageObject($language),

            // placeholders
            '_head' => $this->getHeadCode(),
            '_foot' => $this->getFootCode(),

            // comments & newsletter
            '_comments' => $blogMeta->comments_code ?? '',
            '_newsletter' => $blogMeta->newsletter_code ?? '',
        ];
    }

    /** @return array<string, mixed> */
    private function getRouteVariables(
        Blog $blog,
        Language $language,
        MatchedRoute $matchedRoute,
        Post|Tag|User|null $model,
    ): array {
        $routeName = $matchedRoute->name;

        if ($routeName === 'index') {
            $blogObj = $this->blogObjectFactory->create($blog, $language);

            $featuredPosts = $this->postService->getPostsWithFilterQ(
                blog: $blog,
                language: $language,
                filter: 'is_featured=true',
                limit: 30 // hard limit - who has 30 featured posts?
            );

            return [
                '_meta' => new MetaObject($blogObj->name, $blogObj->description, $blogObj->cover_url, $blogObj->url, $blogObj->url),
                '_featured_posts' => array_map(fn($post) => $this->postObjectFactory->create($blog, $post, $language), $featuredPosts['posts'])
            ];
        }

        if (
            $routeName === 'post' ||
            $routeName === 'page' ||
            $routeName === 'preview'
        ) {
            assert($model instanceof Post);

            $postObj = $this->postObjectFactory->create($blog, $model, $language);
            $url = $postObj->url;

            if ($routeName === 'preview') {
                $variant = $model->getVariants()->filter(fn($v) => $v->getLanguage()->getId() === $language->getId())->first();

                if ($variant && $variant->getContentUnsaved()) {
                    $postObj->content = $this->postContentService->getHtml($variant->getContentUnsaved(), $blog);
                }
            }

            return [
                '_meta' => new MetaObject(
                    $postObj->title,
                    $postObj->description,
                    $postObj->featured_image_url,
                    $url,
                    $model->getCanonicalUrl() ?? $url
                ),
                '_post' => $postObj,
            ];
        }

        if ($routeName === 'tag') {
            assert($model instanceof Tag);

            $tagObj = $this->tagObjectFactory->create($model, $blog, $language);
            return [
                '_meta' => new MetaObject($tagObj->name, $tagObj->description, null, $tagObj->url, $tagObj->url),
                '_tag' => $tagObj,
            ];
        }

        if ($routeName === 'author') {
            assert($model instanceof User);

            $authorObj = $this->authorObjectFactory->create($model, $blog, $language);
            return [
                '_meta' => new MetaObject($authorObj->name, $authorObj->bio, $authorObj->picture_url, $authorObj->url, $authorObj->url),
                '_author' => $authorObj,
            ];
        }

        return [];
    }

    private function getPostFilterVariables(
        Blog $blog,
        Language $language,
        MatchedRoute $matchedRoute,
    ): array
    {
        $filter = $matchedRoute->getPostsFilter();

        if ($filter === null) {
            return [];
        }

        $pageNumber = $this->getPageNumber($matchedRoute);

        try {
            $config = $this->themeConfigService->getConfig($blog);
        } catch (ThemeConfigParsingException $e) {
            throw new TemplateRenderingException($e->getMessage(), previous: $e);
        }

        $limit = is_numeric($config['POSTS_PER_PAGINATION'] ?? null) ? (int)$config['POSTS_PER_PAGINATION'] : 10;
        $offset = ($pageNumber - 1) * $limit;

        $result = $this->postService->getPostsWithFilterQ($blog, $language, $filter, $limit, $offset);
        $posts = $result['posts'];
        $total = $result['total'];

        if (empty($posts) && $pageNumber > 1) {
            throw new TemplateRenderingPageNotFoundException();
        }

        $postObjects = [];
        foreach ($posts as $post) {
            $postObjects[] = $this->postObjectFactory->create($blog, $post, $language);
        }

        return [
            '_posts' => $postObjects,
            '_pagination' => new PaginationObject($limit, $pageNumber, $total)
        ];
    }

    private function getPageNumber(MatchedRoute $matchedRoute): int
    {
        $suffix = $matchedRoute->param('suffix') ?? '';
        if (preg_match('/^page\/(\d+)$/', $suffix, $m)) {
            $n = (int)$m[1];
            return $n > 0 ? $n : 1;
        }
        return 1;
    }

    private function getHeadCode(): string
    {
        return (string)file_get_contents($this->projectDir . '/resources/twig/_head.twig');
    }

    private function getFootCode(): string
    {
        return (string)file_get_contents($this->projectDir . '/resources/twig/_foot.twig');
    }
}
