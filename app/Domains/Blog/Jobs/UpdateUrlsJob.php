<?php declare(strict_types=1);

namespace App\Domains\Blog\Jobs;

use App\Domains\Post\Content\ProsemirrorHelper;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateUrlsJob implements ShouldQueue
{

    use Dispatchable;

    public function __construct(
        public Blog $blog,
        public string $oldUrl,
        public string $newUrl
    ) {}

    public function handle() : void
    {
        $this->updateBlogUrls();
        $this->updatePostsMetaUrls();
        $this->updatePostsContentUrls();
        $this->updateAuthorsUrls();
    }

    private function hasOldUrl(mixed $url) : bool
    {
        if (!is_string($url))
            return false;

        return str_starts_with($url, $this->oldUrl);
    }
    private function replaceOldWithNewUrl(string $url) : string
    {
        return str_replace($this->oldUrl, $this->newUrl, $url);
    }

    private function updateBlogUrls() : void
    {

        /** @var object{
         *     logo_url: string,
         *     cover_url: string,
         *     icon_url: string,
         * } $meta
         */
        $meta = $this->blog->getAllMeta();
        $metaUpdate = [];

        if ($this->hasOldUrl($meta->logo_url)) {
            $metaUpdate['logo_url'] = $this->replaceOldWithNewUrl($meta->logo_url);
        }
        if ($this->hasOldUrl($meta->cover_url)) {
            $metaUpdate['cover_url'] = $this->replaceOldWithNewUrl($meta->cover_url);
        }
        if ($this->hasOldUrl($meta->icon_url)) {
            $metaUpdate['icon_url'] = $this->replaceOldWithNewUrl($meta->icon_url);
        }

        if (count($metaUpdate)) {
            $this->blog->setMeta($metaUpdate);
        }

    }

    private function updatePostsMetaUrls() : void
    {

        Post::where('blog_id', $this->blog->id)->chunk(100, function ($posts) {

            /**
             * @var Post $post
             */
            foreach ($posts as $post) {

                if ($post->featured_image_url && $this->hasOldUrl($post->featured_image_url)) {
                    $post->featured_image_url = $this->replaceOldWithNewUrl($post->featured_image_url);
                    $post->save();
                }

            }
        });

    }

    private function updatePostsContentUrls() : void
    {

        PostVariant::join('posts', 'posts.id', '=', 'post_variants.post_id')
            ->where('posts.blog_id', $this->blog->id)
            ->select('post_variants.*')
            ->chunk(100, function ($variants) {

                /**
                 * @var PostVariant $variant
                 */
                foreach ($variants as $variant) {

                    if ($variant->content) {
                        $variant->content = strval(json_encode(
                            ProsemirrorHelper::updateUrls($variant->content, $this->oldUrl, $this->newUrl)
                        ));
                    }

                    if ($variant->content_unsaved) {
                        $variant->content_unsaved = strval(json_encode(
                            ProsemirrorHelper::updateUrls($variant->content_unsaved, $this->oldUrl, $this->newUrl)
                        ));
                    }

                    $variant->save();
                }

            });

    }

    private function updateAuthorsUrls() : void
    {

        User::where('blog_id', $this->blog->id)->chunk(100, function ($users) {

            /**
             * @var User $user
             */
            foreach ($users as $user) {

                if ($user->picture_url && $this->hasOldUrl($user->picture_url)) {
                    $user->picture_url = $this->replaceOldWithNewUrl($user->picture_url);
                    $user->save();
                }

            }

        });

    }

}