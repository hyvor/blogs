<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Models\Blog;

class UserBlogBlogObject
{
    public int $id;
    public string $name;
    public string $subdomain;
    public ?string $plan;
    public int $posts_count;
    public int $users_count = 200;

    public function __construct(Blog $blog)
    {
        $plan = null;
        foreach ($blog->subscriptions as $sub) {
            if ($sub->valid()) {
                $plan = $sub; // $this->subscriptionRepo->getPlanNameByPlanId($sub->paddle_plan);
            }
        }

        $this->id = $blog->id;
        $this->name = $blog->name;
        $this->subdomain = $blog->subdomain;
        $this->plan = $plan;
        $this->posts_count = $blog->posts_count;
    }
}
