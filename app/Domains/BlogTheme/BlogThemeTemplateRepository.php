<?php

namespace App\Domains\BlogTheme;

use App\Data\Enums\DeliveryAPIScopeEnum;
use App\Domains\Theme\ThemeRepository;
use App\Domains\Theme\Types\OutPutDeliveryAPI;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DataAPI\PostObject;
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
        DeliveryAPIScopeEnum $scope,
        Model $model = null,
        ?int $paginationNumber = null,
    ): string|null {

        $templateFiles = BlogThemeRepository::getFilesInFolder($blog->id, ThemeFileFolderEnum::TEMPLATES);

        $loaderArray = [];
        foreach ($templateFiles as $file) {
            $loaderArray[$file->name] = $file->content;
        }

        $scopeVariables = self::getVarsFromScope($blog, $scope, $model, $paginationNumber);

        $vars = [
            '_blog' => new BlogObject($blog),
            '_config' => [],
            '_scope' => $scope,
        ];

        $vars += $scopeVariables;

        $vars = [
            '_head' => self::getHeadCode($vars),
            '_foot' => self::getFootCode(),
        ];

        $fileName = self::getFileNameToRenderFromScope($scope, array_keys($loaderArray));

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

    private static function getVarsFromScope(
        Blog $blog, 
        DeliveryAPIScopeEnum $scope, 
        ?Model $model,
        ?int $page,
    ): array
    {

        if ($scope === DeliveryAPIScopeEnum::INDEX) {
            $page = $page ?? 1;

            $posts = PostRepository::getPostsWithFilterQ(
                $blog->id, null,
                10, $page * 10,
                'published_at', 'DESC'
            )->map(function ($post) use ($blog) {
                return new PostObject($post, $blog);
            });

            /* $featuredPosts = InternalAPICaller::data($blog->subdomain, 'posts', [
                'limit' => 50,
                'filter' => 'is_featured=true'
            ]); */

            return [
                '_posts' => $posts,
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

        }


    }

    private static function getFileNameToRenderFromScope(DeliveryAPIScopeEnum $scope, array $availableFiles)
    {

        if ($scope == DeliveryAPIScopeEnum::INDEX) {
            return 'index.twig';
        } else if ($scope == DeliveryAPIScopeEnum::POST) {
            return 'post.twig';
        } else if ($scope == DeliveryAPIScopeEnum::PAGE) {
            return in_array('page.twig', $availableFiles) ? 'page.twig' : 'post.twig';
        }

    }
}
