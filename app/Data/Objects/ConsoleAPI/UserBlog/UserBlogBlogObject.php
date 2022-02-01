<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Data\Objects\ConsoleAPI\BlogSubscription\SubscriptionObject;
use App\Models\Blog;

class UserBlogBlogObject
{
    public int $id;
    public string $name;
    public string $subdomain;
    public int $posts_count;
    public int $users_count = 200;

    public bool $is_on_trial;
    public ?int $trial_ends_at;

    public bool $subscribed = false;
    public ?SubscriptionObject $subscription = null;

    public function __construct(Blog $blog)
    {
        $plan = null;

        $this->id = $blog->id;
        $this->name = $blog->name;
        $this->subdomain = $blog->subdomain;
        $this->plan = $plan;
        $this->posts_count = $blog->posts_count;

        $this->is_on_trial = false; // $blog->onTrial();
        $this->trial_ends_at = $blog->customer->trial_ends_at?->timestamp;
        $this->subscribed = $blog->subscribed();

        /**
         * This is the last subscription
         * not the active subscription
         */
        $subscription = $blog->subscription();

        if ($subscription) {
            $this->subscription = new SubscriptionObject($subscription);
        }

    }
}
