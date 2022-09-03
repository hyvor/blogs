<?php

namespace App\Stale\Export;

use App\Models\Blog;

trait ExporterTrait
{
    public function __construct(int $blogId)
    {
        $this->blog = Blog::find($blogId);
    }

    protected function getBlog()
    {
        return $this->blog;
    }

    protected function loopPosts($callback, $ret)
    {
        $this->blog->posts()
            ->orderBy('id')
            ->chunk(100, function ($posts) use ($callback, &$ret) {
                foreach ($posts as $post) {
                    $ret = $callback($post, $ret);
                }
            });

        return $ret;
    }

    protected function loopAuthors($callback, $ret)
    {
        $this->blog->users()
            ->orderBy('id')
            ->chunk(100, function ($users) use ($callback, &$ret) {
                foreach ($users as $user) {
                    $ret = $callback($user, $ret);
                }
            });

        return $ret;
    }

    protected function loopTags($callback, $ret)
    {
        $this->blog->tags()
            ->orderBy('id')
            ->chunk(100, function ($tags) use ($callback, &$ret) {
                foreach ($tags as $tag) {
                    $ret = $callback($tag, $ret);
                }
            });

        return $ret;
    }
}
