<?php

namespace App\Domains\BlogTheme;

use App\Data\Enums\DeliveryAPIScopeEnum;
use App\Domains\Theme\ThemeRepository;
use App\Domains\Theme\Types\OutPutDeliveryAPI;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DataAPI\PostObject;
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

        $loader = new TwigArrayLoader($loaderArray);
        $twig = new TwigEnvironment($loader, ['cache' => false]);

        $vars = [
            '_blog' => new BlogObject($blog),
            '_env' => [],
            '_scope' => $scope,
            '_foot' => '<script src="/assets/flashload.js"></script>
            <script data-flashload-skip-replacing>
                FlashLoad.start()
            </script>',
            ...self::getVarsFromScope($blog, $scope, $model, $paginationNumber)
        ];

        $fileName = self::getFileNameToRenderFromScope($scope, array_keys($loaderArray));

        return $twig->render($fileName, $vars);
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

            return [
                '_post' => new PostObject($model, $blog)
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
