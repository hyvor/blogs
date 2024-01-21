<?php declare(strict_types=1);

namespace App\Http\ConsoleApi\Objects\Blog;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\User;
use ReflectionClass;

class BlogListObject
{

    public int $id;
    public UserRoleEnum $role;
    public bool $is_blocked;
    public int $trial_ends_at;
    public string $name;
    public string $subdomain;
    public BlogTypeEnum $type;
    public string $url;
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

        $this->role = $user->role;
        $this->setBlogAttrs($blog);

    }

    private function setBlogAttrs(Blog $blog)
    {
        $this->id = $blog->id;
        $this->is_blocked = $blog->is_blocked;
        $this->trial_ends_at = $blog->trial_ends_at->getTimestamp();
        $this->name = $blog->variants[0]->name ?? 'Unnamed';
        $this->subdomain = $blog->subdomain;
        $this->type = $blog->type;
        $this->url = $blog->url();
        $this->logo_url = $blog->getMeta('logo_url');

        $this->posts_count = $blog->getCount('posts');
        $this->users_count = $blog->getCount('users');

        $this->subscription = $blog->subscription ?
            new SubscriptionObject($blog->subscription) :
            null;
    }

    public static function fromTempBlog(Blog $blog) : self
    {
        $obj = (new ReflectionClass(self::class))
            ->newInstanceWithoutConstructor();

        $obj->role = UserRoleEnum::OWNER;
        $obj->setBlogAttrs($blog);

        return $obj;
    }

}