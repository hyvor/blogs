<?php declare(strict_types=1);

namespace App\Http\ConsoleApi\Objects\DisplayBlog;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Exceptions\SafetyException;
use App\Models\User;

class DisplayBlogObject
{

    public int $id;
    public UserRoleEnum $role;
    public bool $is_blocked;
    public int $trial_ends_at;
    public string $name;
    public string $subdomain;
    public BlogTypeEnum $type;
    public string $base_url;
    public ?string $logo_url;
    public int $posts_count;
    public int $users_count;

    public ?SubscriptionObject $subscription;

    public function __construct(User $user)
    {

        $blog = $user->blog;

        if (!$blog) {
            throw new SafetyException('User does not have a blog');
        }

        $this->id = $blog->id;
        $this->role = $user->role;
        $this->is_blocked = $blog->is_blocked;
        $this->trial_ends_at = $blog->trial_ends_at->getTimestamp();
        $this->name = $blog->variants[0]->name ?? 'Unnamed';
        $this->subdomain = $blog->subdomain;
        $this->type = $blog->type;
        $this->base_url = $blog->url();
        $this->logo_url = $blog->getMeta('logo_url');

        $this->posts_count = $blog->getCount('posts');
        $this->users_count = $blog->getCount('users');

        $this->subscription = $blog->subscription ?
            new SubscriptionObject($blog->subscription) :
            null;

    }

}