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
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Route\PermalinkService;
use App\Service\Theme\ThemeConfigService;
use App\Service\Theme\ThemeFilesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Error\Error;

class TemplateRendererService
{
    public function __construct(
        private EntityManagerInterface $em,
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
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
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
        $vars = $this->getVariables($blog, $language, $route, $matchedRoute, $model, $templateName);

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

        try {
            return $this->twigRendererService->renderFromFiles($loaderArray, $vars, $template);
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
            $tag = $this->em->getRepository(Tag::class)->findOneBy(['blog' => $blog, 'slug' => $slug]);
            if ($tag === null) return false;
            if ($tag->isPrivate() === true) return false;
            return $tag;
        }

        if ($routeName === 'author') {
            if ($slug === null) return false;
            $user = $this->em->getRepository(User::class)->findOneBy(['blog' => $blog, 'slug' => $slug]);
            return $user ?? false;
        }

        if ($routeName === 'post' || $routeName === 'page') {
            if ($slug === null) return false;

            $variant = $this->em->getRepository(PostVariant::class)->findOneBy([
                'language' => $language,
                'slug' => $slug,
            ]);

            if ($variant === null) return false;

            $post = $variant->getPost();

            if ($routeName === 'page' && !$post->isPage()) return false;
            if ($routeName === 'post' && $post->isPage()) return false;
            if ($post->getBlog()->getId() !== $blog->getId()) return false;

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
     */
    private function getVariables(
        Blog $blog,
        Language $language,
        Route $route,
        MatchedRoute $matchedRoute,
        Post|Tag|User|null $model,
        string $templateName,
    ): array {
        $vars  = $this->getDefaultVariables($blog, $language);
        $vars['_route'] = new RouteObject($route, $matchedRoute, $templateName);

        $filter = $route->getPostsFilter();
        $resolvedFilter = null;
        if ($filter !== null) {
            $resolvedFilter = preg_replace_callback('/\{(.+)\}/', function ($matches) use ($matchedRoute) {
                $var = $matches[1];
                $param = $matchedRoute->param($var) ?? '';
                return "'$param'";
            }, $filter);
        }

        $vars += $this->getRouteVariables($blog, $language, $route, $matchedRoute, $model, $resolvedFilter);

        if ($resolvedFilter !== null) {
            $pageNumber = $this->getPageNumber($matchedRoute);
            $config = $this->themeConfigService->getConfig($blog);
            $limit = is_numeric($config['POSTS_PER_PAGINATION'] ?? null) ? (int)$config['POSTS_PER_PAGINATION'] : 10;
            $offset = ($pageNumber - 1) * $limit;

            $result = $this->postService->getPostsWithFilter($blog, $language, $resolvedFilter, $limit, $offset);
            $posts = $result['posts'];
            $total = $result['total'];

            if (empty($posts) && $pageNumber > 1) {
                throw new TemplateRenderingPageNotFoundException();
            }

            $postObjects = [];
            foreach ($posts as $post) {
                $postObjects[] = $this->postObjectFactory->create($blog, $post, $language);
            }

            $vars['_posts'] = $postObjects;
            $vars['_pagination'] = new PaginationObject($limit, $pageNumber, $total);
        }

        /** @var array<string, mixed> $serialized */
        $serialized = json_decode((string)json_encode($vars), true);
        return $serialized;
    }

    private function getDefaultVariables(Blog $blog, Language $language): array
    {
        $config = $this->themeConfigService->getConfig($blog);
        $blogObject = $this->blogObjectFactory->create($blog, $language);

        return [
            '_blog' => $blogObject,
            '_config' => $config,
            '_lang' => new LanguageObject($language),
            '_head' => $this->getHeadCode(),
            '_foot' => $this->getFootCode(),
        ];
    }

    /** @return array<string, mixed> */
    private function getRouteVariables(
        Blog $blog,
        Language $language,
        Route $route,
        MatchedRoute $matchedRoute,
        Post|Tag|User|null $model,
        ?string $resolvedFilter,
    ): array {
        $routeName = $matchedRoute->name;

        if ($routeName === 'index') {
            $blogObj = $this->blogObjectFactory->create($blog, $language);
            $url = $this->permalinkService->getBlogPermalink($blog, $language);
            return [
                '_meta' => new MetaObject($blogObj->name, $blogObj->description, $blogObj->cover_url, $url, $url),
            ];
        }

        if (
            (
                $routeName === 'post' ||
                $routeName === 'page' ||
                $routeName === 'preview'
            )
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
                '_comments' => '',
                '_newsletter' => '',
            ];
        }

        if ($routeName === 'tag') {
            assert($model instanceof Tag);

            $tagObj = $this->tagObjectFactory->create($model, $blog, $language);
            return [
                '_meta' => new MetaObject($tagObj->name, $tagObj->name, null, $tagObj->url, $tagObj->url),
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
