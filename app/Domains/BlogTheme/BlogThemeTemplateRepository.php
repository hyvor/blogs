<?php

namespace App\Domains\BlogTheme;

use App\Data\Enums\DeliveryAPIScopeEnum;
use App\Domains\Theme\ThemeRepository;
use App\Domains\Theme\Types\OutPutDeliveryAPI;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DataAPI\BlogObject;
use App\Helpers\InternalAPICaller;
use App\Models\Blog;
use Twig\Loader\ArrayLoader as TwigArrayLoader;
use Twig\Environment as TwigEnvironment;

class BlogThemeTemplateRepository
{

    public static function renderFile(
        Blog $blog, 
        DeliveryAPIScopeEnum $scope,
        ?string $slug,
        ?int $page 
    ) : string 
    {

        $templateFiles = BlogThemeRepository::getFilesInFolder($blog->id, ThemeFileFolderEnum::TEMPLATES);

        $loaderArray = [];
        foreach ($templateFiles as $file) {
            $loaderArray[$file->name] = $file->content;
        }

        $loader = new TwigArrayLoader($loaderArray);
        $twig = new TwigEnvironment($loader);

        $vars = [
            '@blog' => new BlogObject($blog),
            '@env' => [],
            ...self::getVarsFromScope($blog, $scope, $page)
        ];

        $fileName = self::getFileNameToRenderFromScope($scope, array_keys($loaderArray));

        return $twig->render($fileName, $vars);

    }

    private static function getVarsFromScope(Blog $blog, DeliveryAPIScopeEnum $scope, ?int $page) : array {

        if ($scope === DeliveryAPIScopeEnum::INDEX) {

            $page = $page ?? 1;

            $posts = InternalAPICaller::data($blog->subdomain, 'posts', [
                'limit' => 10,
                'page' => $page,
            ]);

            $featuredPosts = InternalAPICaller::data($blog->subdomain, 'posts', [
                'limit' => 50,
                'filter' => 'is_featured=true'
            ]);

            return [
                '@posts' => $posts,
                '@featured_posts' => $featuredPosts
            ];

        }

    }

    private static function getFileNameToRenderFromScope( DeliveryAPIScopeEnum $scope, array $availableFiles) {

        if ($scope == DeliveryAPIScopeEnum::INDEX) {
            return 'index.twig';
        }

    }


}
