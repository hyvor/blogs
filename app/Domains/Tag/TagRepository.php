<?php

namespace App\Domains\Tag;

use App\Domains\Language\LanguageRepository;
use App\Helpers\CollectionWithTotal;
use App\Models\Blog;
use App\Models\Language;
use App\Models\PostTag;
use App\Models\Tag;
use App\Models\TagVariant;
use Hyvor\FilterQ\Facades\FilterQ;
use Illuminate\Database\Eloquent\Collection;

class TagRepository
{
    /**
     * Get tags of a blog.
     *
     * @param $blog
     * @param int $limit
     * @param int $offset
     * @return Collection<Tag>
     */
    public static function getTags($blog, int $limit, int $offset = 0): Collection
    {
        $language = LanguageRepository::getPrimaryLanguage($blog);

        $tags = Tag::where('blog_id', '=', $blog->id)
            ->join('tag_variants', function ($join) use ($language) {
                $join->on('tag_variants.tag_id', '=', 'tags.id');
                $join->where('tag_variants.language_id', '=', $language->id);
            })
            ->select('tags.*')
            ->limit($limit)
            ->offset($offset)
            ->latest()
            ->get();

        return $tags;
    }

    public static function getTagByBlogIdAndIdentifier(int $blogId, ?int $id, ?string $slug): ?Tag
    {
        $tag = Tag::where('blog_id', $blogId);
        if ($id) {
            $tag->where('id', $id);
        } else {
            $tag->where('slug', $slug);
        }

        return $tag->first();
    }

    public static function getTagByBlogIdAndSlug(int $blogId, string $slug): ?Tag
    {
        return self::getTagByBlogIdAndIdentifier($blogId, null, $slug);
    }

    public static function getTagsWithFilterQ(
        Blog $blog,
        ?string $filter,
        int $limit,
        int $offset,
        array $orderBys = [
            ['tags.posts_count', 'DESC'],
        ],
    ): CollectionWithTotal {
        $builder = FilterQ::expression($filter)
            ->builder(Tag::class)
            ->keys(function ($keys) {
                $keys->add('id')
                    ->column('tags.id')
                    ->valueType('int');

                $keys->add('slug')
                    ->column('tags.slug')
                    ->valueType('string|int')
                    ->operators('=,!=');

                $keys->add('posts_count')
                    ->column('tags.posts_count')
                    ->valueType('int');

                $keys->add('created_at')
                    ->column('tags.created_at')
                    ->valueType('date');
            })
            ->addWhere();

        foreach ($orderBys as $orderBy) {
            $builder->orderBy($orderBy[0], $orderBy[1]);
        }

        $tags = $builder
            ->where('tags.blog_id', $blog->id)
            ->limit($limit)
            ->offset($offset)
            ->get();

        $total = $builder->count();

        return new CollectionWithTotal($tags, $total);
    }

    public static function createTag(Blog $blog, string $name): Tag
    {
        $tag = Tag::create([
            'blog_id' => $blog->id,
            'slug' => UniqueSlugGenerator::generate($blog, [$name]),
        ]);

        $language = LanguageRepository::getPrimaryLanguage($blog);

        TagVariant::create([
            'tag_id' => $tag->id,
            'language_id' => $language->id,
            'name' => $name,
        ]);

        return $tag;
    }

    public static function updateTag(
        int $id,
        int $languageId,
        string $slug,
        ?string $codeHead,
        ?string $codeFoot,
        ?string $name,
        ?string $description
    ): void {
        $tag = Tag::find($id)
        ->update([
            'slug' => $slug,
            'code_head' => $codeHead,
            'code_foot' => $codeFoot,
        ]);

        TagVariant::where('tag_id', '=', $id)
            ->where('language_id', '=', $languageId)
            ->update([
                'name' => $name,
                'description' => $description,
            ]);

        // return $tag;
    }

    public static function deleteTag($tagId, $languageId): void
    {
        $language = Language::where('id', '=', $languageId)
        ->value('is_primary');

        if ($language == 0) {
            TagVariant::where('tag_id', '=', $tagId)
                ->where('language_id', '=', $languageId)
                ->delete();
        } else {
            TagVariant::where('tag_id', '=', $tagId)
                ->where('language_id', '=', $languageId)
                ->delete();

            Tag::find($tagId)
                ->delete();
        }
    }

    /*
    *
    * this functions are used for the tag_variants table
    *
    */
    public static function createTagVariant($tagId, $languageId)
    {
        $language = Language::where('id', '=', $languageId)
        ->value('is_primary');

        if ($language == 0) {
            $tagVariantCheck = TagVariant::where('tag_id', '=', $tagId)
            ->where('language_id', '=', $languageId)
            ->first();

            if ($tagVariantCheck == null) {
                TagVariant::create([
                    'tag_id' => $tagId,
                    'language_id' => $languageId,
                ]);
            }
        }
    }


    /*
    *
    * this function will create and save the tag
    *
    */
    public static function createSaveTag($blogId, $postId, $tagId)
    {
        $createPostTag = PostTag::create([
            'post_id' => $postId,
            'tag_id' => $tagId,
        ]);

        return $createPostTag;
    }


    /*public static function getPostTag($postId, $tagId)
    {
        // dd('tests');
        // dd('Post Id '+$postId + ' Tag Id '+$tagId);
        // dd($postId);

        // $connection = DB::table('tags')
        //     ->select('tags.name')
        //     ->join('post_tag', 'tags.id' ,$tagId.'', '=', 'post_tag.tag_id')
        //     ->join('posts', 'posts.id', $postId.'', '=', 'post_tag.post_id')
        //     ->get();

        $connection = DB::table('tags')
            ->select('tags.name')
            ->join('post_tag', 'tags.id' ,$tagId ,'=', 'post_tag.tag_id')
            ->join('posts', 'posts.id', $postId ,'=', 'post_tag.post_id')
            ->get();
        // $connection = DB::table('tags')
        //     ->select('tags.name')
        //     ->join('post_tag', 'tags.id', '=', 'post_tag.tag_id')
        //     ->join('posts', 'posts.id', '=', 'post_tag.post_id')
        //     ->get();
        return $connection;
    }*/
}
