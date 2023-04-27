<?php declare(strict_types=1);

namespace App\Domains\Tag;

use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostTagAuthorRepository;
use App\Domains\Tag\Events\TagCreatedEvent;
use App\Domains\Tag\Events\TagDeletedEvent;
use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Domains\Tag\Events\TagVariantCreatedEvent;
use App\Domains\Tag\Events\TagVariantDeletedEvent;
use App\Domains\Tag\Events\TagVariantUpdatedEvent;
use App\Helpers\CollectionWithTotal;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Tag;
use App\Models\TagVariant;
use Hyvor\FilterQ\FilterQ;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TagRepository
{
    /**
     * Get tags of a blog
     * @return Collection<int, Tag>
     */
    public static function getTags(Blog $blog, int $limit, int $offset = 0): Collection
    {
        $language = LanguageRepository::getPrimaryLanguage($blog);

        return Tag::where('blog_id', '=', $blog->id)
            ->join('tag_variants', function ($join) use ($language) {
                $join->on('tag_variants.tag_id', '=', 'tags.id');
                $join->where('tag_variants.language_id', '=', $language->id);
            })
            ->select('tags.*')
            ->limit($limit)
            ->offset($offset)
            ->latest()
            ->get();
    }

    /**
     * @param array<string[]> $orderBys
     * @return CollectionWithTotal<Tag>
     */
    public static function getTagsWithFilterQ(
        Blog $blog,
        ?string $filter,
        int $limit,
        int $offset,
        array $orderBys = [
            ['tags.posts_count', 'DESC'],
        ],
    ): CollectionWithTotal
    {

        /** @var Builder<Tag> $builder */
        $builder = (new FilterQ)->expression($filter)
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
            ->select('tags.*')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $total = $builder->count();

        return new CollectionWithTotal($tags, $total);
    }

    /**
     * @return Collection<int, Tag>
     */
    public static function searchTags(Blog $blog, string $search, int $limit) : Collection
    {

        $search = str_replace('%', '', $search); // to make it safe
        $search .= '%';

        $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog);

        return Tag::join(
            'tag_variants',
            fn ($join) => $join->on('tag_variants.tag_id', '=', 'tags.id')
                ->where('tag_variants.language_id', '=', $primaryLanguage->id)
        )
            ->where('tags.blog_id', $blog->id)
            ->where('tag_variants.name', 'LIKE', $search)
            ->limit($limit)
            ->select('tags.*')
            ->get();

    }

    public static function createTag(Blog $blog, string $name): Tag
    {
        $tag = Tag::create([
            'blog_id' => $blog->id,
            'slug' => UniqueBlogItemSlugGenerator::generate($blog, [$name]),
        ]);

        $language = LanguageRepository::getPrimaryLanguage($blog);

        TagVariant::create([
            'tag_id' => $tag->id,
            'language_id' => $language->id,
            'name' => $name,
        ]);

        $tag->refresh();

        TagCreatedEvent::dispatch($tag);

        return $tag;
    }

    /**
     * @param array<string, mixed> $updates
     */
    public static function updateTag(Tag $tag, array $updates): Tag
    {
        foreach ($updates as $key => $value) {
            $tag->$key = $value;
        }

        $tag->save();

        TagUpdatedEvent::dispatch($tag);

        return $tag;
    }

    public static function deleteTag(Tag $tag): void
    {
        // delete variants
        $tag->variants->map(fn ($variant) => self::deleteTagVariant($variant));

        // delete post-tags
        PostTagAuthorRepository::deletePostTagsByTag($tag);

        // delete tag
        $tag->delete();

        TagDeletedEvent::dispatch($tag);
    }

    public static function createTagVariant(Tag $tag, Language $language): TagVariant
    {
        $variant = TagVariant::create([
            'tag_id' => $tag->id,
            'language_id' => $language->id,
        ]);

        TagVariantCreatedEvent::dispatch($variant->refresh());

        return $variant;
    }

    /**
     * @param array<string, mixed> $updates
     */
    public static function updateTagVariant(TagVariant $variant, array $updates): TagVariant
    {
        foreach ($updates as $key => $value) {
            $variant->$key = $value;
        }
        $variant->save();

        TagVariantUpdatedEvent::dispatch($variant);

        return $variant;
    }

    public static function deleteTagVariant(TagVariant $variant) : void
    {
        $variant->delete();

        TagVariantDeletedEvent::dispatch($variant);
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

    public static function getTagVariantByTagIdAndLanguageId(int $tagId, int $languageId): ?TagVariant
    {
        return TagVariant::where('tag_id', $tagId)
            ->where('language_id', $languageId)
            ->first();
    }
}
