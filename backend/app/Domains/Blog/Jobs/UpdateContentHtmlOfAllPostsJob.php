<?php

namespace App\Domains\Blog\Jobs;

use App\Domains\Post\Content\PostContentService;
use App\Domains\Post\PostRepository;
use App\Models\Blog;
use App\Models\PostVariant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateContentHtmlOfAllPostsJob implements ShouldQueue
{

    use Dispatchable;

    public function __construct(
        public Blog $blog,
        /**
         * @var (callable(int $count, int $firstId, int $lastId) : void)|null
         */
        public $onProgress = null
    ) {}

    public function handle() : void
    {

        PostVariant::join('posts', 'posts.id', '=', 'post_variants.post_id')
            ->where('posts.blog_id', $this->blog->id)
            /**
             * Get 100 each time and update
             */
            ->select('post_variants.*')
            ->chunk(100, function($variants) {

                foreach ($variants as $variant) {
                    PostRepository::updateVariantHtml($variant);
                }

                if ($this->onProgress) {
                    ($this->onProgress)($variants->count(), (int) $variants->first()?->id, (int) $variants->last()?->id);
                }

            });

    }

}
