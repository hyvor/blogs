<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Data\Enums\BlogBillingTypeEnum;
use App\Data\Enums\BlogIntegrationEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Subscription\SubscriptionService;
use App\Models\Blog;

class UserBlogBlogObject
{
    public int $id;

    public bool $is_blocked;

    public int $trial_ends_at;

    public string $name;

    public string $subdomain;

    public BlogTypeEnum $type;

    public BlogBillingTypeEnum $billing_type;

    public ?BlogIntegrationEnum $integration;

    public string $base_url;

    public ?string $logo_url;

    public int $posts_count;

    public int $users_count;

    // public int $trial_ends_at;

    public ?SubscriptionObject $subscription = null;

    public function __construct(Blog $blog)
    {
        $this->id = $blog->id;
        $this->is_blocked = $blog->is_blocked;
        $this->trial_ends_at = $blog->trial_ends_at->getTimestamp();
        $this->name = $blog->variants[0]->name ?? 'Unnamed';
        $this->subdomain = $blog->subdomain;
        $this->type = $blog->type;
        $this->billing_type = $blog->billing_type;
        $this->integration = $blog->integration;
        $this->base_url = PermalinkRepository::getFullUrlFromPath($blog);

        $logoUrl = $blog->getMeta('logo_url');
        $this->logo_url = $logoUrl ? strval($logoUrl) : null;

        $this->posts_count = $blog->getCount('posts');
        $this->users_count = $blog->getCount('users');

        $subscription = SubscriptionService::getActiveBlogSubscription($blog);

        if ($subscription) {
            $this->subscription = new SubscriptionObject($subscription);
        }
    }
}
