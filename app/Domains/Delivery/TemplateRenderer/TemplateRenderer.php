<?php declare(strict_types=1);

namespace App\Domains\Delivery\TemplateRenderer;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DataAPI\AuthorObject;
use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DataAPI\LanguageObject;
use App\Data\Objects\DataAPI\PaginationObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Data\Objects\DataAPI\TagObject;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Data\Objects\DeliveryAPI\MetaObject;
use App\Data\Objects\DeliveryAPI\RouteObject;
use App\Domains\App\DomainService;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Post\PostRepository;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Tag\TagRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\User\UserRepository;
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Twig\Error\Error;

class TemplateRenderer
{

    use TemplateRendererTrait;

    private PathMatcher $pathMatcher;

    private MatchedRoute $matchedRoute;

    private ?string $filter;

    private string $templateName;

    private Tag|User|Post|null|false $model;

    public function __construct(
        PathMatcher $pathMatcher,
        MatchedRoute $matchedRoute,
        ?string $filter
    ) {
        $this->pathMatcher = $pathMatcher;
        $this->matchedRoute = $matchedRoute;
        $this->filter = $filter;
    }

    public function getResponseObject(): ?DeliveryAPIResponseObject
    {
        if (! isset($this->model)) {
            $this->model = $this->getModel();
        }

        // required model was not found
        if ($this->model === false) {
            return null;
        }

        try {
            $this->setConfig($this->pathMatcher->blog);
            $output = $this->render();
        } catch (Error $e) {
            return $this->getRenderErrorResponseObject($e->getMessage());
        } catch (TemplatePageNotFoundException) {
            return null;
        }

        return DeliveryAPIResponseObject::forFile(DeliveryAPIFileTypeEnum::TEMPLATE, $output);
    }

    private function render(): string
    {

        // ready files loader array
        $templateFiles = ThemeFilesRepository::getFilesInFolder(
            $this->pathMatcher->blog,
            ThemeFileFolderEnum::TEMPLATES
        );

        $loaderArray = [];
        foreach ($templateFiles as $file) {
            $loaderArray[$file->name] = $file->content;
        }

        $this->setTemplateName(array_keys($loaderArray));

        // get vars
        $vars = $this->getVariables();

        return TwigRenderer::renderFromFiles($loaderArray, $vars, $this->templateName);
    }

    /**
     * @throws Error
     * @throws TemplatePageNotFoundException
     * @return array<mixed>
     */
    private function getVariables() : array
    {
        $blog = $this->pathMatcher->blog;

        $blogObject = new BlogObject($blog, $this->pathMatcher->language);

        $vars = $this->getDefaultVariables($blog, $this->pathMatcher->language);

        $vars += [
            '_route' => new RouteObject($this->matchedRoute, $this->templateName),
        ];

        $vars += $this->getRouteVariables();

        if ($this->filter !== null) {
            ['posts' => $posts, 'pagination' => $pagination] = $this->getPostsAndPagination();

            $vars['_posts'] = $posts;
            $vars['_pagination'] = $pagination;
        }

        /**
         * JSON encoding + decoding is to make sure only data from objects are sent
         * and the developer does not have access to PHP methods
         * @var array<mixed> $variables
         */
        $variables = json_decode((string) json_encode($vars), true);

        return $variables;
    }

    /**
     * @return array<string, mixed>
     */
    private function getRouteVariables(): array
    {
        $routeName = $this->matchedRoute->name;

        if ($routeName === 'index') {
            $blogObject = new BlogObject($this->pathMatcher->blog, $this->pathMatcher->language);

            return [
                '_meta' => new MetaObject(
                    $blogObject->name,
                    $blogObject->description,
                    $blogObject->cover_url,
                    $blogObject->url,
                    $blogObject->url
                ),
                '_featured_posts' => PostRepository::getPostsWithFilterQ(
                    blog: $this->pathMatcher->blog,
                    language: $this->pathMatcher->language,
                    filter: $this->filter,
                    limit: 30 // hard limit - who has 30 featured posts?
                )->collection,
            ];
        } elseif (
            (
                $routeName === 'post' ||
                $routeName === 'page' ||
                $routeName === 'preview'
            ) &&
            $this->model instanceof Post
        ) {
            if ($routeName === 'preview') {

                // set content_html to content_unsaved if it is set

                $variant = $this->model->variants
                    ->firstWhere('language_id', $this->pathMatcher->language->id);

                if ($variant && $variant->content_unsaved) {
                    $variant->content_html = PostContentRepository::getHtml(
                        $variant->content_unsaved,
                        $this->pathMatcher->blog
                    );
                }
            }

            $postObject = new PostObject($this->model, $this->pathMatcher->blog, $this->pathMatcher->language);

            return [
                '_meta' => new MetaObject(
                    $postObject->title,
                    $postObject->description,
                    $postObject->featured_image_url,
                    $postObject->url,
                    $postObject->canonical_url ?? $postObject->url
                ),
                '_post' => $postObject,
                '_comments' => $this->pathMatcher->blog->getMeta('comments_code') ?? '',
                '_newsletter' => $this->pathMatcher->blog->getMeta('newsletter_code') ?? '',
            ];
        } elseif ($routeName === 'tag' && $this->model instanceof Tag) {
            $tagObject = new TagObject($this->model, $this->pathMatcher->blog, $this->pathMatcher->language);

            return [
                '_meta' => new MetaObject(
                    $tagObject->name,
                    $tagObject->name,
                    null,
                    $tagObject->url,
                    $tagObject->url
                ),
                '_tag' => $tagObject,
            ];
        } elseif ($routeName === 'author' && $this->model instanceof User) {
            $authorObject = new AuthorObject($this->model, $this->pathMatcher->blog, $this->pathMatcher->language);

            return [
                '_meta' => new MetaObject(
                    $authorObject->name,
                    $authorObject->bio,
                    $authorObject->picture_url,
                    $authorObject->url,
                    $authorObject->url
                ),
                '_author' => $authorObject,
            ];
        }

        return [];
    }

    /**
     * @throws TemplatePageNotFoundException
     * @return array{posts: Collection<int, PostObject>, pagination: PaginationObject}
     */
    private function getPostsAndPagination() : array
    {
        $pageNumber = $this->getPageNumber();

        $limit = $this->config['POSTS_PER_PAGINATION'] ?? 10;
        $offset = ($pageNumber - 1) * $limit;

        $collectionWithTotal = PostRepository::getPostsWithFilterQ(
            blog: $this->pathMatcher->blog,
            language: $this->pathMatcher->language,
            filter: $this->filter,
            limit: $limit,
            offset: $offset,
            orderBys: [
                ['posts.is_featured', 'DESC'],
                ['posts.published_at', 'DESC'],
            ]
        );

        if (count($collectionWithTotal->collection) === 0 && $pageNumber > 1) {
            throw new TemplatePageNotFoundException();
        }

        return [
            'posts' => $collectionWithTotal->collection->map(function ($post) {
                return new PostObject($post, $this->pathMatcher->blog, $this->pathMatcher->language);
            }),
            'pagination' => new PaginationObject($limit, $pageNumber, $collectionWithTotal->total),
        ];
    }

    private function getPageNumber(): int
    {
        $suffix = strval($this->matchedRoute->param('suffix'));

        if (preg_match('/^page\/(\d+)$/', $suffix, $matches)) {
            $number = (int) $matches[1];

            return $number > 0 ? $number : 1;
        }

        return 1;
    }

    /**
     * @param string[] $availableFiles
     */
    private function setTemplateName(array $availableFiles) : void
    {
        $checkFiles = explode(',', $this->matchedRoute->route->template ?? '');

        foreach ($checkFiles as $file) {
            $file = trim($file).'.twig';
            if (in_array($file, $availableFiles)) {
                $this->templateName = $file;

                return;
            }
        }

        $this->templateName = 'index.twig';
    }

    /**
     * Post|Tag|User - a model
     * null - no model for this route
     * false - there should be a model, but couldn't find it. So, return 404
     */
    private function getModel(): Post|Tag|User|null|false
    {
        $slug = $this->matchedRoute->param('slug');

        if ($this->matchedRoute->name === 'tag') {
            $model = TagRepository::getTagByBlogIdAndSlug($this->pathMatcher->blog->id, $slug);

            return $model !== null ? $model : false;
        } elseif ($this->matchedRoute->name === 'author') {
            $model = UserRepository::getUserByBlogIdAndSlug($this->pathMatcher->blog->id, $slug);

            return $model !== null ? $model : false;
        } elseif ($this->matchedRoute->name === 'post' || $this->matchedRoute->name === 'page') {

            $post = PostRepository::getPostByLanguageAndSlug(
                $this->pathMatcher->language,
                $slug
            );

            if ($post) {

                // if page was matched, it should be a post
                // don't worry, PathMatcher will re-match the page if post and page has the same match
                if (
                    ($this->matchedRoute->name === 'page' && ! $post->is_page) ||
                    ($this->matchedRoute->name === 'post' && $post->is_page)
                ) {
                    return false;
                }

                // post permalink should be valid
                $validPermalink = PermalinkRepository::validatePostPermalink($post, $this->matchedRoute->params);
                if (! $validPermalink) {
                    return false;
                }

                return $post;
            }

            return false;
        }

        return null;
    }

    // preview repository sets model before getting response object
    public function setModel(Post $model) : void
    {
        $this->model = $model;
    }

    public function getRenderErrorResponseObject(string $message): DeliveryAPIResponseObject
    {
        $response = <<<HTML
            <div style="font-family:monospace;">
                Twig Template Error:<br><br>
                <div style="font-size:18px">$message</div>
            </div>
        HTML;

        return DeliveryAPIResponseObject::forFile(
            DeliveryAPIFileTypeEnum::TEMPLATE,
            $response,
            'text/html',
            false,
            500
        );
    }

}
