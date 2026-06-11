<?php

namespace App\Api\Console\Object;

use App\Entity\Enum\BlogType;
use App\Entity\User;

class BlogListObject
{
    public int $id;
    public string $role;
    public bool $is_blocked;
    public string $name;
    public string $subdomain;
    public BlogType $type;
    public string $url;
    public ?string $logo_url;
    public int $posts_count;
    public int $users_count;

    public function __construct(User $user, string $url)
    {
        $blog = $user->getBlog();
        $this->id = $blog->getId();
        $this->role = $user->getRole()->value;
        $this->is_blocked = $blog->isBlocked();
        $variants = $blog->getVariants();
        $this->name = $variants[0]?->getName() ?? 'Unnamed';
        $this->subdomain = $blog->getSubdomain();
        $this->type = $blog->getType();
        $this->url = $url;

        $meta = $blog->getMeta();
        $this->logo_url = $meta->logo_url;
        $counts = $blog->getCounts() ?? [];
        $this->posts_count = (int)($counts['posts'] ?? 0);
        $this->users_count = (int)($counts['users'] ?? 0);
    }
}
