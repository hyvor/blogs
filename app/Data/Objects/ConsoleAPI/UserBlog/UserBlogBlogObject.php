<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Data\Objects\ConsoleAPI\BlogSubscription\BlogSubscriptionObject;
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

        $this->is_on_trial = $blog->onTrial();
        $this->subscribed = $blog->subscribed();

        $subscription = $blog->subscription();

        if ($subscription) {
            $this->subscription = new SubscriptionObject($subscription);
        }

    }
}
