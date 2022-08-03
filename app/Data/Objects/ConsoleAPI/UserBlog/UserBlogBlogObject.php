<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;

class UserBlogBlogObject
{
    public int $id;

    public string $name;

    public string $subdomain;

    public BlogTypeEnum $type;

    public string $base_url;

    public ?string $logo_url;

    public int $posts_count;

    public int $users_count;

    public bool $is_on_trial;

    public ?int $trial_ends_at;

    public ?SubscriptionObject $subscription = null;

    public function __construct(Blog $blog)
    {
        $this->id = $blog->id;
        $this->name = $blog->variants[0]->name;
        $this->subdomain = $blog->subdomain;
        $this->type = $blog->type;
        $this->base_url = PermalinkRepository::getFullUrlFromPath($blog);
        $this->logo_url = $blog->logo_url;

        $this->posts_count = $blog->getCount('posts');
        $this->users_count = $blog->getCount('users');

        $this->is_on_trial = $blog->onTrial();
        $this->trial_ends_at = $blog->customer->trial_ends_at?->timestamp;

        $subscription = $blog->subscription();

        if ($subscription && $subscription->valid()) {
            $this->subscription = new SubscriptionObject($subscription);
        }
    }
}
