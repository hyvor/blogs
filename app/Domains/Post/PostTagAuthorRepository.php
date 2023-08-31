<?php declare(strict_types=1);

namespace App\Domains\Post;

use App\Domains\Language\LanguageRepository;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Tag\TagRepository;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\Tag;
use App\Models\User;

class PostTagAuthorRepository
{

    /**
     * @param  Post  $post
     * @param  int[]  $ids
     * @return void
     */
    public static function updateTags(Post $post, array $ids)
    {
        $dbCount = Tag::where('blog_id', $post->blog_id)
            ->whereIn('id', $ids)
            ->count();

        if (count($ids) !== $dbCount) {
            throw new TrustedException('Some users are missing or IDs are wrong');
        }

        /** @var int[] $currentIds */
        $currentIds = PostTag::where('post_id', $post->id)
            ->pluck('tag_id')
            ->toArray();

        // remove all
        PostTag::where('post_id', $post->id)->delete();

        // add new
        foreach ($ids as $id) {
            PostTag::create([
                'post_id' => $post->id,
                'tag_id' => $id,
            ]);
        }

        $changedIds = array_unique(array_merge($currentIds, $ids));

        self::updateTagPostCounts($changedIds);

        PostUpdatedEvent::dispatch($post);
    }

    /**
     * @param int[] $ids
     */
    private static function updateTagPostCounts(array $ids) : void
    {
        foreach ($ids as $id) {

            $tag = Tag::find($id);

            if (!$tag)
                continue;

            $blog = $tag->blog;

            if (!$blog)
                continue;

            $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog);

            $postsCount = PostTag::where('tag_id', $id)
                ->join('posts', 'post_tag.post_id', '=', 'posts.id')
                ->join('post_variants', 'posts.id', '=', 'post_variants.post_id')
                ->where('post_variants.language_id', $primaryLanguage->id)
                ->where('post_variants.status', 'published')
                ->where('posts.is_page', 0)
                ->count();

            TagRepository::updateTag($tag, [
                'posts_count' => $postsCount,
            ]);

        }
    }

    public static function deletePostTagsByTag(Tag $tag) : void
    {
        PostTag::where('tag_id', $tag->id)->delete();
    }

    /**
     * @param  Post  $post
     * @param  array<integer>  $ids
     * @return void
     */
    public static function updateAuthors(Post $post, array $ids)
    {
        $dbCount = User::where('blog_id', $post->blog_id)
            ->whereIn('id', $ids)
            ->count();

        if (count($ids) !== $dbCount) {
            throw new TrustedException('Some users are missing or IDs are wrong');
        }

        $currentIds = PostAuthor::where('post_id', $post->id)
            ->pluck('user_id')
            ->toArray();

        // remove all
        PostAuthor::where('post_id', $post->id)->delete();

        // add new
        foreach ($ids as $id) {
            PostAuthor::create([
                'post_id' => $post->id,
                'user_id' => $id,
            ]);
        }

        /** @var int[] $changedIds */
        $changedIds = array_unique(array_merge($currentIds, $ids));

        self::updateAuthorPostCounts($changedIds);

        PostUpdatedEvent::dispatch($post);
    }

    /**
     * @param int[] $ids
     */
    private static function updateAuthorPostCounts(array $ids) : void
    {
        foreach ($ids as $id) {
            $user = User::find($id);
            if (!$user)
                continue;

            $blog = $user->blog;
            if (!$blog)
                continue;

            $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog);

            $postsCount = PostAuthor::where('user_id', $id)
                ->join('posts', 'post_author.post_id', '=', 'posts.id')
                ->join('post_variants', 'posts.id', '=', 'post_variants.post_id')
                ->where('post_variants.language_id', $primaryLanguage->id)
                ->where('post_variants.status', 'published')
                ->where('posts.is_page', 0)
                ->count();

            UserRepository::updateUser($user, [
                'posts_count' => $postsCount,
            ]);
        }
    }

    public static function createAuthor(int $postId, int $userId) : PostAuthor
    {
        return PostAuthor::create([
            'post_id' => $postId,
            'user_id' => $userId,
        ]);
    }

    public static function deletePostAuthorsByUser(User $user) : void
    {
        PostAuthor::where('user_id', $user->id)->delete();
    }
}
