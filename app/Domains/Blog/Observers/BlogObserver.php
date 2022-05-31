<?php

namespace App\Domains\Blog\Observers;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\NavigationFiller;
use App\Domains\Blog\Fillers\PostsFiller;
use App\Domains\Blog\Fillers\RouteFiller;
use App\Domains\Blog\Fillers\TagsFiller;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use Illuminate\Support\Facades\App;

class BlogObserver
{
    public function created(Blog $blog)
    {

        if (App::environment('testing'))
            return;

        // FILL NEW BLOG

        /**
         * Start Trial
         * ============
         */
        $blog->createAsCustomer([
            'trial_ends_at' => now()->addDays(config('limits.trial_days')),
        ]);


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


        $fillers = [
            LanguageFiller::class,
            TagsFiller::class,
            RouteFiller::class,
            NavigationFiller::class,
            PostsFiller::class,
        ];
        foreach ($fillers as $filler) {
            (new $filler($blog))->fill();
        }

    }

    public function updated(Blog $blog)
    {
    }
}
