<?php

namespace App\Domains\Blog\Observers;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\NavigationFiller;
use App\Domains\Blog\Fillers\PostsFiller;
use App\Domains\Blog\Fillers\RouteFiller;
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
            'trial_ends_at' => now()->addDays(config('limits.trial_days')),
        ]);


        $fillers = [
            LanguageFiller::class,
            RouteFiller::class,
            NavigationFiller::class,
            PostsFiller::class,
        ];
        foreach ($fillers as $filler) {
            (new $filler($blog))->fill();
        }

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
    }

    public function updated(Blog $blog)
    {
    }
}
