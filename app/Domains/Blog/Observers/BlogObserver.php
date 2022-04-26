<?php
namespace App\Domains\Blog\Observers;



use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Language\LanguageRepository;
use App\Domains\Route\RouteRepository;
use App\Domains\User\UserRepository;
use App\Models\Blog;

class BlogObserver
{

    public function created(Blog $blog)
    {

        // FILL NEW BLOG
        
        /**
         * Start Trial
         * ============
         */
        $blog->createAsCustomer([
            'trial_ends_at' => now()->addDays(config('limits.trial_days'))
        ]);
        
        /**
         * Add Primary Language (DEFAULT)
         * ============
         */
        LanguageRepository::createLanguage(
            $blog,
            LanguageRepository::DEFAULT_LANGUAGE_CODE,
            LanguageRepository::DEFAULT_LANGUAGE_NAME,
            true
        );

        /**
         * Add the user as an owner
         * ============
         */
        UserRepository::createUser(
            $blog, 
            $blog->hyvor_user_id, 
            UserRoleEnum::OWNER, 
            UserStatusEnum::ACTIVE
        );

        /**
         * Add Routes
         * ============
         */
        foreach (RouteRepository::DEFAULT_ROUTES as $route) {
            RouteRepository::createRoute(
                $blog,
                $route['name'],
                $route['match'],
                $route['template'],
                $route['posts_filter'] ?? null
            );
        }
        
        /**
         * Add Default Posts
         */
        
        
        
    }

    public function updated(Blog $blog)
    {

        

    }

}
