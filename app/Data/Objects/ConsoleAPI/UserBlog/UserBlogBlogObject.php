<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Data\Enums\CountEnum;
use App\Data\Objects\ConsoleAPI\BlogSubscription\SubscriptionObject;
use App\Domains\Count\CountRepository;
use App\Models\Blog;

class UserBlogBlogObject
{
    public int $id;
    public string $name;
    public string $subdomain;
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

    public function __construct(Blog $blog)
    {
        $plan = null;

        $this->id = $blog->id;
        $this->name = $blog->name;
        $this->subdomain = $blog->subdomain;
        $this->plan = $plan;

        $counts = CountRepository::getCounts($blog, ['users', 'posts']);
        $this->posts_count = $counts['users'];
        $this->users_count = $counts['posts'];

        $this->is_on_trial = $blog->onTrial();
        $this->trial_ends_at = $blog->customer->trial_ends_at?->timestamp;
        $this->subscribed = $blog->subscribed();

        $subscription = $blog->subscription();

        if ($subscription) {
            $this->subscription = new SubscriptionObject($subscription);
        }

    }
}
