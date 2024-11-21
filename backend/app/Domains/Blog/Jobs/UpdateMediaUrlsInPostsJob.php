<?php

namespace App\Domains\Blog\Jobs;

use App\Domains\Cache\CacheService;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Post\Content\UrlUpdater;
use App\Domains\Post\PostRepository;
use App\Models\Blog;
use App\Models\PostVariant;
use Hyvor\Phrosemirror\Document\Node;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateMediaUrlsInPostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Blog $blog,
        public string $oldUrl,
        public string $newUrl
    )
    {
    }

    public function handle(): void
    {
        PostVariant::join('posts', 'posts.id', '=', 'post_variants.post_id')
            ->where('posts.blog_id', $this->blog->id)
            ->select('post_variants.*')
            ->chunk(100, function ($posts) {

                /**
                 * @var PostVariant $variant
                 */
                foreach ($posts as $variant) {
                    $updates = [];

                    if ($variant->content) {
                        $updates['content'] = $this->updateContentUrl($variant->content);
                    }
                    if ($variant->content_unsaved) {
                        $updates['content_unsaved'] = $this->updateContentUrl($variant->content_unsaved);
                    }

                    if ($updates) {
                        PostRepository::updatePostVariant($variant, $updates);
                    }
                }
            });

        app(CacheService::class, ['blog' => $this->blog])->clearTemplateCache();
    }

    private function updateContentUrl(string $content): string
    {

        $doc = PostContentService::getDocumentFromJson($content, $this->blog);
        return (new UrlUpdater($doc))
            ->update(mediaUpdater: function(Node $media) {
                /** @var string $src */
                $src = $media->attrs->get('src',false);

                if ($src !== $this->oldUrl) {
                    return false;
                }

                return $this->newUrl;
            })
            ->toJson();

    }
}
