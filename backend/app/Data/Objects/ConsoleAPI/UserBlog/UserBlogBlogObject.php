<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;

class UserBlogBlogObject
{
    public int $id;

    public bool $is_blocked;

    public int $trial_ends_at;

    public string $name;

    public string $subdomain;

    public BlogTypeEnum $type;

    public string $base_url;

    public ?string $logo_url;

    public int $posts_count;

    public int $users_count;

    // public int $trial_ends_at;

    public function __construct(Blog $blog)
    {
        $this->id = $blog->id;
        $this->is_blocked = $blog->is_blocked;
        $this->trial_ends_at = $blog->trial_ends_at->getTimestamp();
        $this->name = $blog->variants[0]->name ?? 'Unnamed';
        $this->subdomain = $blog->subdomain;
        $this->type = $blog->type;
        $this->base_url = PermalinkRepository::getFullUrlFromPath($blog);

        $logoUrl = $blog->getMeta('logo_url');
        $this->logo_url = $logoUrl ? strval($logoUrl) : null;

        $this->posts_count = $blog->getCount('posts');
        $this->users_count = $blog->getCount('users');
    }
}
