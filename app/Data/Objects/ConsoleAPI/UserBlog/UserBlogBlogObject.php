<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Data\Enums\BlogBillingTypeEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Subscription\SubscriptionService;
use App\Models\Blog;

class UserBlogBlogObject
{
    public int $id;

    public string $name;

    public string $subdomain;

    public BlogTypeEnum $type;

    public BlogBillingTypeEnum $billing_type;

    public string $base_url;

    public ?string $logo_url;

    public int $posts_count;

    public int $users_count;

    // public int $trial_ends_at;

    public ?SubscriptionObject $subscription = null;

    public function __construct(Blog $blog)
    {
        $this->id = $blog->id;
        $this->name = $blog->variants[0]->name;
        $this->subdomain = $blog->subdomain;
        $this->type = $blog->type;
        $this->billing_type = $blog->billing_type;
        $this->base_url = PermalinkRepository::getFullUrlFromPath($blog);
        $this->logo_url = $blog->logo_url;

        $this->posts_count = $blog->getCount('posts');
        $this->users_count = $blog->getCount('users');

        // $this->trial_ends_at = $blog->trial_ends_at->timestamp;
        $subscription = SubscriptionService::getActiveBlogSubscription($blog);

        if ($subscription) {
            $this->subscription = new SubscriptionObject($subscription);
        }
    }
}
