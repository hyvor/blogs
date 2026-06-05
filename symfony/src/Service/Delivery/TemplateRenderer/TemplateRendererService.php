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
use App\Entity\PostAuthor;
use App\Entity\PostTag;
use App\Entity\PostVariant;
use App\Entity\Route;
use App\Entity\Tag;
use App\Entity\TagVariant;
use App\Entity\User;
use App\Entity\UserVariant;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\PostQueryService;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Route\PermalinkService;
use App\Service\Theme\ThemeConfigService;
use App\Service\Theme\ThemeFilesService;
use Doctrine\ORM\EntityManagerInterface;

class TemplateRendererService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PermalinkService $permalinkService,
        private ThemeFilesService $themeFilesService,
        private ThemeConfigService $themeConfigService,
        private TwigRendererService $twigRendererService,
        private PostQueryService $postQueryService,
        private BlogObjectFactory $blogObjectFactory,
        private PostObjectFactory $postObjectFactory,
        private TagObjectFactory $tagObjectFactory,
        private AuthorObjectFactory $authorObjectFactory,
        private string $projectDir,
    ) {}

    public function render(
        Blog $blog,
        Language $language,
        Route $route,
        MatchedRoute $matchedRoute,
    ): ?DeliveryResponse {
        $model = $this->getModel($blog, $language, $route, $matchedRoute);
        if ($model === false) {
            return null;
        }

        $templateFiles = $this->themeFilesService->getFilesInFolder($blog, ThemeFileFolder::TEMPLATES);
        $loaderArray = [];
        foreach ($templateFiles as $file) {
            $loaderArray[$file->getName()] = $file->getContent() ?? '';
        }

        $templateName = $this->getTemplateName($route, array_keys($loaderArray));

        if (!array_key_exists($templateName, $loaderArray)) {
            return null;
        }

        try {
            $vars = $this->getVariables($blog, $language, $route, $matchedRoute, $model, $templateName);
            $content = $this->twigRendererService->renderFromFiles($loaderArray, $vars, $templateName);
        } catch (\Twig\Error\Error $e) {
            $msg = $e->getMessage();
            $html = "<div style=\"font-family:monospace;\">Twig Template Error:<br><br><div style=\"font-size:18px\">$msg</div></div>";
            return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $html, 'text/html', 500, false);
        } catch (TemplatePageNotFoundException) {
            return null;
        }

        return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $content);
    }

    /** @param string[] $availableFiles */
    private function getTemplateName(Route $route, array $availableFiles): string
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

            if (!$this->permalinkService->validatePostPermalink($post, $matchedRoute->params)) {
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
     */
    private function getVariables(
        Blog $blog,
        Language $language,
        Route $route,
        MatchedRoute $matchedRoute,
        Post|Tag|User|null $model,
        string $templateName,
    ): array {
        $config = $this->themeConfigService->getConfig($blog);

        $blogObject = $this->blogObjectFactory->create($blog, $language);

        $vars = [
            '_blog' => $blogObject,
            '_config' => $config,
            '_lang' => new LanguageObject($language),
            '_head' => $this->getHeadCode(),
            '_foot' => $this->getFootCode(),
            '_route' => new RouteObject($route, $matchedRoute, $templateName),
        ];

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
            $limit = is_numeric($config['POSTS_PER_PAGINATION'] ?? null) ? (int)$config['POSTS_PER_PAGINATION'] : 10;
            $offset = ($pageNumber - 1) * $limit;

            $result = $this->postQueryService->getPostsWithFilter($blog, $language, $resolvedFilter, $limit, $offset);
            $posts = $result['posts'];
            $total = $result['total'];

            if (empty($posts) && $pageNumber > 1) {
                throw new TemplatePageNotFoundException();
            }

            $postObjects = [];
            foreach ($posts as $post) {
                $pv = null;
                foreach ($post->getVariants() as $v) {
                    if ($v->getLanguageId() === $language->getId()) {
                        $pv = $v;
                        break;
                    }
                }
                if ($pv === null || $pv->getStatus() !== PostVariantStatus::PUBLISHED) continue;
                $postObjects[] = $this->buildPostObject($post, $pv, $blog, $language);
            }

            $vars['_posts'] = $postObjects;
            $vars['_pagination'] = new PaginationObject($limit, $pageNumber, $total);
        }

        /** @var array<string, mixed> $serialized */
        $serialized = json_decode((string)json_encode($vars), true);
        return $serialized;
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
        $routeName = $route->getName();

        if ($routeName === 'index') {
            $blogObj = $this->blogObjectFactory->create($blog, $language);
            $url = $this->permalinkService->getBlogPermalink($blog, $language);
            return [
                '_meta' => new MetaObject($blogObj->name, $blogObj->description, $blogObj->cover_url, $url, $url),
            ];
        }

        if (($routeName === 'post' || $routeName === 'page') && $model instanceof Post) {
            $variant = null;
            foreach ($model->getVariants() as $v) {
                if ($v->getLanguageId() === $language->getId()) {
                    $variant = $v;
                    break;
                }
            }
            if ($variant === null) return [];

            $postObj = $this->buildPostObject($model, $variant, $blog, $language);
            $url = $this->permalinkService->getPostPermalink($model, $blog, $language);
            return [
                '_meta' => new MetaObject($postObj->title, $postObj->description, $postObj->featured_image_url, $url, $model->getCanonicalUrl() ?? $url),
                '_post' => $postObj,
                '_comments' => '',
                '_newsletter' => '',
            ];
        }

        if ($routeName === 'tag' && $model instanceof Tag) {
            $tagObj = $this->buildTagObject($model, $blog, $language);
            return [
                '_meta' => new MetaObject($tagObj->name, $tagObj->name, null, $tagObj->url, $tagObj->url),
                '_tag' => $tagObj,
            ];
        }

        if ($routeName === 'author' && $model instanceof User) {
            $authorObj = $this->buildAuthorObject($model, $blog, $language);
            return [
                '_meta' => new MetaObject($authorObj->name, $authorObj->bio, $authorObj->picture_url, $authorObj->url, $authorObj->url),
                '_author' => $authorObj,
            ];
        }

        return [];
    }

    private function getPageNumber(MatchedRoute $matchedRoute): int
    {
        $suffix = (string)($matchedRoute->param('suffix') ?? '');
        if (preg_match('/^page\/(\d+)$/', $suffix, $m)) {
            $n = (int)$m[1];
            return $n > 0 ? $n : 1;
        }
        return 1;
    }

    private function buildPostObject(Post $post, PostVariant $variant, Blog $blog, Language $language): \App\Api\Data\Object\PostObject
    {
        $postTags = $this->em->getRepository(PostTag::class)->findBy(['post' => $post]);
        $tags = array_map(fn($pt) => $pt->getTag(), $postTags);

        $postAuthors = $this->em->getRepository(PostAuthor::class)->findBy(['post' => $post]);
        $authors = array_filter(array_map(fn($pa) => $pa->getUser(), $postAuthors));

        $otherVariants = [];
        foreach ($post->getVariants() as $v) {
            if ($v->getLanguageId() !== $language->getId() && $v->getStatus() === PostVariantStatus::PUBLISHED) {
                $vLang = $this->em->getRepository(Language::class)->find($v->getLanguageId());
                if ($vLang) {
                    $otherVariants[] = ['variant' => $v, 'language' => $vLang];
                }
            }
        }

        return $this->postObjectFactory->create($post, $variant, $blog, $language, $tags, array_values($authors), $otherVariants);
    }

    private function buildTagObject(Tag $tag, Blog $blog, Language $language): \App\Api\Data\Object\TagObject
    {
        $variants = $this->em->getRepository(TagVariant::class)->findBy(['tag' => $tag]);
        $variantData = [];
        foreach ($variants as $v) {
            $vLang = $this->em->getRepository(Language::class)->find($v->getLanguageId());
            if ($vLang) {
                $variantData[] = ['language' => $vLang, 'name' => $v->getName(), 'description' => $v->getDescription()];
            }
        }
        return $this->tagObjectFactory->create($tag, $blog, $language, $variantData);
    }

    private function buildAuthorObject(User $user, Blog $blog, Language $language): \App\Api\Data\Object\AuthorObject
    {
        $variants = $this->em->getRepository(UserVariant::class)->findBy(['user' => $user]);
        $variantData = [];
        foreach ($variants as $v) {
            $vLang = $this->em->getRepository(Language::class)->find($v->getLanguageId());
            if ($vLang) {
                $variantData[] = ['language' => $vLang, 'name' => $v->getName(), 'bio' => $v->getBio(), 'location' => $v->getLocation()];
            }
        }
        return $this->authorObjectFactory->create($user, $blog, $language, $variantData);
    }

    private function getHeadCode(): string
    {
        $path = $this->projectDir . '/resources/twig/_head.twig';
        return file_exists($path) ? (string)file_get_contents($path) : '';
    }

    private function getFootCode(): string
    {
        $path = $this->projectDir . '/resources/twig/_foot.twig';
        return file_exists($path) ? (string)file_get_contents($path) : '';
    }
}
