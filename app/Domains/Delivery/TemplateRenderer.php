<?php
namespace App\Domains\Delivery;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Data\Objects\DataAPI\TagObject;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Data\Objects\DeliveryAPI\MetaObject;
use App\Domains\ThemeFiles\ThemeFilesRepository;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Post\Content\PostSearchRepository;
use App\Domains\Post\PostRepository;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Tag\TagRepository;
use App\Domains\User\UserRepository;
use App\Models\Language;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;
use Twig\Error\Error;

class TemplateRenderer {

    private PathMatcher $pathMatcher;
    private MatchedRoute $matchedRoute;
    private ?string $filter;

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

    public function getResponseObject() : ?DeliveryAPIResponseObject
    {

        if (!isset($this->model)) {
            $this->model = $this->getModel();
        }

        // required model was not found
        if ($this->model === false) {
            return null;
        }

        $output = $this->render();

        return DeliveryAPIResponseObject::forFile($output);

    }

    private function render()
    {

        // get vars
        $vars = $this->getVariables();
     
        
        // ready files loader array
        $templateFiles = ThemeFilesRepository::getFilesInFolder(
            $this->pathMatcher->getThemable(), 
            ThemeFileFolderEnum::TEMPLATES
        );

        $loaderArray = [];
        foreach ($templateFiles as $file) {
            $loaderArray[$file->name] = $file->content;
        }

        $fileName = $this->getFileNameToRender(array_keys($loaderArray));

        try {
            $html = TwigRenderer::renderFromFiles($loaderArray, $vars, $fileName);
        } catch (Error $e) {
            $html = $this->getRenderError($e->getMessage());
        }

        return $html;
    }

    private function getVariables() {
        $blog = $this->pathMatcher->blog;

        $blogObject = new BlogObject($blog);
        $scopeVariables = $this->getRouteVariables($blogObject);

        $vars = [
            '_blog' => $blogObject,
            '_config' => [],
            '_route' => $this->matchedRoute->name,
            '_lang' => $this->pathMatcher->language->code
        ];

        $vars += $scopeVariables;

        $vars += [
            '_head' => $this->getHeadCode($vars),
            '_foot' => $this->getFootCode($vars),
        ];

        $vars['_posts'] = $this->getPosts();

        /**
         * This is to make sure only data from objects are sent
         * and the developer does not have access to PHP methods
         */
        $vars = json_decode(json_encode($vars), true);

        return $vars;

    }

    private function getRouteVariables() : array
    {

        $routeName = $this->matchedRoute->name;

        if ($routeName === 'index') {

            $blogObject = new BlogObject($this->pathMatcher->blog);

            return [
                '_meta' => new MetaObject(
                    $blogObject->name,
                    $blogObject->description,
                    $blogObject->featured_image,
                    $blogObject->url,
                    $blogObject->url
                ),
                '_featured_posts' => PostRepository::getPostsWithFilterQ(
                    blog: $this->pathMatcher->blog,
                    language: $this->pathMatcher->language,
                    filter: $this->filter,
                    limit: 30, // hard limit - who has 30 featured posts?
                )['posts']
            ];

        } else if ($routeName === 'post' || $routeName === 'page' || $routeName === 'preview') {

            $postObject = new PostObject($this->model, $this->pathMatcher->blog, $this->pathMatcher->language);
            return [
                '_meta' => new MetaObject(
                    $postObject->title,
                    $postObject->description,
                    $postObject->featured_image,
                    $postObject->url,
                    $postObject->canonical_url ?? $postObject->url
                ),
                '_post' => $postObject,
            ];

        } else if ($routeName === 'tag') {

            $tagObject = new TagObject($this->model, $this->pathMatcher->blog);

            return [
                '_meta' => new MetaObject(
                    $tagObject->name,
                    $tagObject->name,
                    $tagObject->featured_image,
                    $tagObject->url,
                    $tagObject->url
                ),
                '_tag' => $tagObject,
            ];

        }

        return [];

    }

    private function getPosts() {

        $pageNumber = $this->getPageNumber();

        $limit = 10;
        $offset = ($pageNumber - 1) * 10;

        if ($this->matchedRoute->name === 'search') {

            $search = $this->matchedRoute->param('search');

            $searchData = PostSearchRepository::search(
                search: $search,
                limit: $limit,
                offset: $offset,
                blogId: $this->pathMatcher->blog->id,
                languageId: $this->pathMatcher->language->id,
                isPage: false,
                isPublished: true
            );

            $postCollection = $searchData['posts'];

        } else {
            $postCollection = PostRepository::getPostsWithFilterQ(
                blog: $this->pathMatcher->blog, 
                language: $this->pathMatcher->language,
                filter: $this->filter,
                limit: $limit,
                offset: $offset
            )['posts'];
        }

        return $postCollection->map(function ($post) {
            return new PostObject($post, $this->pathMatcher->blog, $this->pathMatcher->language);
        });

    }

    private function getPageNumber() : int {

        $suffix = $this->matchedRoute->param('suffix');

        if (preg_match('/^page\/(\d+)$/', $suffix, $matches)) {
            $number = (int) $matches[1];
            return $number > 0 ? $number : 1;
        }

        return 1;

    }

    private function getHeadCode($vars)
    {
        return file_get_contents(resource_path('twig/_head.twig'));

        return TwigRenderer::renderFile(resource_path('twig/_head.twig'), $vars);
    }

    private function getFootCode() 
    {
        return '<script src="/assets/flashload.js"></script>
        <script data-flashload-skip-replacing>
            FlashLoad.start()
        </script>';
    }


    private function getFileNameToRender(array $availableFiles)
    {

        $checkFiles = explode(',', $this->matchedRoute->route->template);
    
        foreach ($checkFiles as $file) {

            $file = trim($file) . '.twig';
            if (in_array($file, $availableFiles)) {
                return $file;
            }

        }

        return 'index.twig';

    }

    /**
     * Post|Tag|User - a model
     * null - no model for this route
     * false - there should be a model, but couldn't find it. So, return 404
     */
    private function getModel() : Post|Tag|User|null|false {

        $slug = $this->matchedRoute->param('slug');

        if ($this->matchedRoute->name === 'tag') {

            $model = TagRepository::getTagByBlogIdAndSlug($this->pathMatcher->blog->id, $slug);

            return $model !== null ? $model : false;

        } else if ($this->matchedRoute->name === 'author') {

            $model = UserRepository::getUserByBlogIdAndSlug($this->pathMatcher->blog->id, $slug);

            return $model !== null ? $model : false;

        } else if ($this->matchedRoute->name === 'post' || $this->matchedRoute->name === 'page') {

            $post = PostRepository::getPostByBlogIdAndSlug(
                $this->pathMatcher->blog->id, 
                $slug
            );

            if ($post) {

                // if page was matched, it should be a post
                // don't worry, PathMatcher will re-match the page if post and page has the same match
                if (
                    ($this->matchedRoute->name === 'page' && !$post->is_page) ||
                    ($this->matchedRoute->name === 'post' && $post->is_page)
                ) {
                    return false;
                }

                // post permalink should be valid
                $validPermalink = PermalinkRepository::validatePostPermalink($post, $this->matchedRoute->params);
                if (!$validPermalink) {
                    return false;
                }

                return $post;
            }

            return false;
            
        }

        return null;

    }

    // preview repository sets model before getting response object
    public function setModel($model) {
        $this->model = $model;
    }

    public function getRenderError($message) {
        return <<<HTML
            <div style="font-family:monospace;">
                Twig Template Error:<br><br>
                <div style="font-size:18px">$message</div>
            </div>
        HTML;
    }

}