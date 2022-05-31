<?php

namespace App\Domains\Blog\Observers;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\NavigationFiller;
use App\Domains\Blog\Fillers\PostFiller;
use App\Domains\Blog\Fillers\RouteFiller;
use App\Domains\Blog\Fillers\TagFiller;
use App\Domains\Blog\Fillers\UserFiller;
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

        $fillers = [
            LanguageFiller::class,

            UserFiller::class,
            TagFiller::class,
            PostFiller::class,

            RouteFiller::class,
            NavigationFiller::class,
        ];
        foreach ($fillers as $filler) {
            (new $filler($blog))->fill();
        }

    }

    public function updated(Blog $blog)
    {
    }
}
