<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\LinkStatusTypeEnum;
use App\Domains\LinkAnalyzer\PostVariantsCheck\OnStartEvent;
use App\Domains\LinkAnalyzer\PostVariantsCheck\PostVariantsCheck;
use App\Models\Blog;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;

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
        PostVariant::join('posts', 'posts.id', '=', 'post_variants.post_id')
            ->where('posts.blog_id', $this->blog->id)
            ->where('post_variants.status', PostStatusEnum::PUBLISHED)
            ->orderBy('post_variants.id')
            ->select(
                'post_variants.id',
                'post_variants.content',
                'post_variants.language_id',
            )
            ->chunk(1000, function ($variants) {
                $this->checkPostVariants($variants);
            });
    }

    /**
     * @param Collection<int, PostVariant> $variants
     */
    private function checkPostVariants(Collection $variants): void
    {
        Event::listen(OnStartEvent::class, function (OnStartEvent $event) {
            //
        });

        $postsCheck = new PostVariantsCheck(
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