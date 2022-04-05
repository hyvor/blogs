<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Data\Enums\CountEnum;
use App\Data\Objects\ConsoleAPI\BlogSubscription\SubscriptionObject;
use App\Data\Objects\ConsoleAPI\LanguageObject;
use App\Domains\Blog\BlogRepository;
use App\Domains\Count\CountRepository;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;

class UserBlogBlogObject
{
    public int $id;
    // public string $name;
    public string $subdomain;
    public string $base_url;
    public int $posts_count;
    public int $users_count;

    public bool $is_on_trial;
    public ?int $trial_ends_at;

    public bool $subscribed = false;

    /**
     * This is the last subscription
     * not the active subscription
     * it can be a deleted subscription
     * 
     * Therefore use $this->subscribed to make sure the user is subscribed
     */
    public ?SubscriptionObject $subscription = null;

    public LanguageObject $default_language;

    public function __construct(Blog $blog)
    {
        $plan = null;

        $this->id = $blog->id;
        $this->name = $blog->name;
        $this->subdomain = $blog->subdomain;
        $this->base_url = PermalinkRepository::getBlogPermalink($blog);
        $this->plan = $plan;

        $counts = CountRepository::getCounts($blog, [
            CountEnum::BLOG_USERS,
            CountEnum::BLOG_POSTS
        ]);
        $this->posts_count = $counts[ CountEnum::BLOG_POSTS->value ];
        $this->users_count = $counts[ CountEnum::BLOG_USERS->value ];

        $this->is_on_trial = $blog->onTrial();
        $this->trial_ends_at = $blog->customer->trial_ends_at?->timestamp;
        $this->subscribed = $blog->subscribed();

        $subscription = $blog->subscription();

        if ($subscription) {
            $this->subscription = new SubscriptionObject($subscription);
        }

        $this->default_language = new LanguageObject(
            $blog->languages()
                ->where('is_primary', true)
                ->first()
        );

    }
}
