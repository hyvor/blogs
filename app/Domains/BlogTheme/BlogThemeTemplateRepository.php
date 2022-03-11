<?php

namespace App\Domains\BlogTheme;

use App\Data\Enums\DeliveryAPIScopeEnum;
use App\Domains\Theme\ThemeRepository;
use App\Domains\Theme\Types\OutPutDeliveryAPI;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Data\Objects\DataAPI\TagObject;
use App\Data\Objects\DeliveryAPI\MetaObject;
use App\Domains\BlogTheme\Twig\Renderer;
use App\Domains\Post\PostRepository;
use App\Helpers\InternalAPICaller;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Model;
use Twig\Loader\ArrayLoader as TwigArrayLoader;
use Twig\Environment as TwigEnvironment;

class BlogThemeTemplateRepository
{
    public static function renderFile(
        Blog $blog,
        string $template,
        ?DeliveryAPIScopeEnum $scope,
        ?Model $model,
        ?string $filter = null,
        ?int $pageNumber = null,
    ): string|null {

        $templateFiles = BlogThemeRepository::getFilesInFolder($blog->id, ThemeFileFolderEnum::TEMPLATES);

        $loaderArray = [];
        foreach ($templateFiles as $file) {
            $loaderArray[$file->name] = $file->content;
        }

        $blogObject = new BlogObject($blog);
        $scopeVariables = self::getVarsFromScope($blogObject, $scope, $model, $pageNumber);

        $vars = [
            '_blog' => $blogObject,
            '_config' => [],
            '_scope' => $scope->value,
        ];

        $vars += $scopeVariables;

        $vars += [
            '_head' => self::getHeadCode($vars),
            '_foot' => self::getFootCode($vars),
        ];

        if ($filter !== null) {
            $vars['_posts'] = self::getPosts($blog, $filter, $pageNumber);
        }

        $fileName = self::getFileNameToRender($template, array_keys($loaderArray));

        return Renderer::renderFromFiles($loaderArray, $vars, $fileName);
    }

    private static function getHeadCode(array $vars) {
        return Renderer::renderFile(resource_path('twig/_head.twig'), $vars);
    }

    private static function getFootCode() {

        return '<script src="/assets/flashload.js"></script>
        <script data-flashload-skip-replacing>
            FlashLoad.start()
        </script>';

    }

    private static function getPosts(
        Blog $blog,
        string $filter,
        int $pageNumber
    ) {

        return PostRepository::getPostsWithFilterQ(
            $blog->id, $filter,
            10, ($pageNumber - 1) * 10,
            'published_at', 'DESC'
        )->map(function ($post) use ($blog) {
            return new PostObject($post, $blog);
        });

    }

    private static function getVarsFromScope(
        BlogObject $blogObject, 
        ?DeliveryAPIScopeEnum $scope, 
        ?Model $model,
        int $pageNumber = null,
    ): array
    {

        $blog = $blogObject->getBlog();

        if ($scope === DeliveryAPIScopeEnum::INDEX) {

            /* $featuredPosts = InternalAPICaller::data($blog->subdomain, 'posts', [
                'limit' => 50,
                'filter' => 'is_featured=true'
            ]); */
        
            return [
                '_meta' => new MetaObject(
                    $blogObject->name,
                    $blogObject->description,
                    $blogObject->featured_image,
                    $blogObject->url,
                    $blogObject->url
                ),
                '_featured_posts' => null, // $featuredPosts->data
            ];

        } else if ($scope === DeliveryAPIScopeEnum::POST || $scope === DeliveryAPIScopeEnum::PAGE) {

            $postObject = new PostObject($model, $blog);
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

        } else if ($scope === DeliveryAPIScopeEnum::TAG) {

            $tagObject = new TagObject($model, $blog);

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


    }

    /**
     * $template = page,post
     */
    private static function getFileNameToRender(string $template, array $availableFiles)
    {

        $checkFiles = explode(',', $template);
    
        foreach ($checkFiles as $file) {

            $file = trim($file) . '.twig';
            if (in_array($file, $availableFiles)) {
                return $file;
            }

        }

        return 'index.twig';

    }
}
