<?php
namespace App\Domains\Blog;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Language\LanguageRepository;
use App\Domains\Route\RouteRepository;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\User;
use Illuminate\Support\Facades\Lang;

/**
 * Fills a new blog with data
 */
class FillNewBlog {

    public static function fill(Blog $blog) {

        self::addTrial($blog);

        $user = self::fillOwner($blog);
        // dd($user);

        self::fillRoutes($blog);
        self::fillPosts($blog, $user);
        self::fillPosts($blog);
        $language = self::fillLanguage($blog); 


        return [
            'user' => $user,
            'language' => $language
        ];

    }

    private static function addTrial(Blog $blog) {
        $blog->createAsCustomer([
            'trial_ends_at' => now()->addDays(config('limits.trial_days'))
        ]);
    }

    // private static function fillOwner(Blog $blog) : User {
    private static function fillOwner(Blog $blog) {
        // create the user (owner)
        return UserRepository::createUser($blog, $blog->user_id, UserRoleEnum::OWNER, UserStatusEnum::ACTIVE);
    }

    // add default routes
    public static function fillRoutes(Blog $blog) {

        $defaultRoutes = [
            // post
            [
                'name' => 'post',
                'match' => '/{slug}',
                'template' => 'post'
            ],
            // page
            [
                'name' => 'page',
                'match' => '/{slug}',
                'template' => 'page,post'
            ],
            // home page (index)
            [
                'name' => 'index',
                'match' => '/',
                'template' => 'index',
                'posts_filter' => ''
            ],
            // tag
            [
                'name' => 'tag',
                'match' => '/tag/{slug}',
                'template' => 'tag,index',
                'posts_filter' => 'tag.slug = {slug}',
            ],
            // author
            [
                'name' => 'author',
                'match' => '/author/{slug}',
                'template' => 'author,index',
                'posts_filter' => 'author.slug = {slug}'
            ],
            // search
            [
                'name' => 'search',
                'match' => '/search',
                'template' => 'search,index'
            ]
        ];


        foreach ($defaultRoutes as $route) {
            RouteRepository::createRoute(
                $blog,
                $route['name'],
                $route['match'],
                $route['template'],
                $route['posts_filter'] ?? null,
                $route['content_type'] ?? null
            );
        }

    }

    public static function fillLanguage(Blog $blog) : Language {
        return LanguageRepository::createLanguage(
            $blog,
            LanguageRepository::DEFAULT_LANGUAGE_CODE,
            LanguageRepository::DEFAULT_LANGUAGE_NAME,
            true
        );
    }

    // add default posts
    public static function fillPosts(Blog $blog) {



    }

}