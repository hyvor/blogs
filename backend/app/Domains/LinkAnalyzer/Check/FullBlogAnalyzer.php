<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Domains\LinkAnalyzer\LinkStatusTypeEnum;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Collection;

class FullBlogAnalyzer
{

    public int $postsCount = 0;
    public int $pagesCount = 0;
    public int $postVariantsCount = 0;
    public int $pageVariantsCount = 0;

    public int $linksCount = 0;
    public int $linksOkCount = 0;
    public int $linksBrokenCount = 0;
    public int $linksRedirectCount = 0;
    public int $linksIgnoredCount = 0;

    public function __construct(
        private Blog $blog,
    ) {
    }

    public function analyze(): void
    {
        Post::where('blog_id', $this->blog->id)
            ->orderBy('id')
            ->chunk(100, function ($posts) {
                $this->checkPosts($posts);
            });
    }

    /**
     * @param Collection<int, Post> $posts
     */
    private function checkPosts(Collection $posts): void
    {
        $postsCheck = new PostsCheck(
            $this->blog,
            onPostStart: function (Post $post) {
                if ($post->is_page) {
                    $this->pagesCount++;
                } else {
                    $this->postsCount++;
                }
            },
            onPostVariantStart: function (PostVariant $variant, Post $post) {
                if ($post->is_page) {
                    $this->pageVariantsCount++;
                } else {
                    $this->postVariantsCount++;
                }
            },
            onLinksUpdate: function (PostVariant $variant, $links) {
                $this->linksCount += $links->count();

                // update the counts
                foreach ($links as $link) {
                    $statusType = LinkStatusTypeEnum::fromStatus($link->status_code);

                    if ($link->ignore) {
                        $this->linksIgnoredCount++;
                    } elseif ($statusType === LinkStatusTypeEnum::OK) {
                        $this->linksOkCount++;
                    } elseif ($statusType === LinkStatusTypeEnum::BROKEN) {
                        $this->linksBrokenCount++;
                    } elseif ($statusType === LinkStatusTypeEnum::REDIRECT) {
                        $this->linksRedirectCount++;
                    }
                }
            }
        );

        $postsCheck->check($posts);
    }

}