<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use Illuminate\Database\Eloquent\Model;


class NavigationDeleter implements DeleterInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function delete() : ?Model
    {
        NavigationVariant::join('navigations', 'navigations.id', '=', 'navigation_variants.navigation_id')
            ->where('navigations.blog_id', $this->blog->id)
            ->delete();

        Navigation::where('blog_id', $this->blog->id)->delete();
    }
}
