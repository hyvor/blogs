<?php

namespace App\Domains\Blog;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Media\MediaRepository;
use App\Domains\Route\PermalinkRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Language;

class BlogRepository
{
    public static function createBlog(
        int $userId,
        string $name,
        string $subdomain,
        BlogTypeEnum $type = BlogTypeEnum::DEFAULT
    ) : Blog {

        $blog = Blog::create([
            'hyvor_user_id' => $userId,
            'subdomain' => $subdomain,
            'type' => $type->value,
        ]);

        BlogVariant::create([
            'blog_id' => $blog->id,
            'language_id' => $blog->languages[0]->id,
            'name' => $name
        ]);

        return $blog;
    }

    public static function getBlogById(int $id): ?Blog
    {
        return Blog::find($id);
    }

    public static function getBlogBySubdomain(string $subdomain): ?Blog
    {
        return Blog::where('subdomain', $subdomain)->first();
    }

    public static function getBlogByCustomDomain(string $customDomain): ?Blog
    {
        return Blog::where('hosting_domain', $customDomain)->first();
    }

    /**
     * @param Blog $blog
     * @param array<string, mixed> $update
     * @return Blog
     */
    public static function updateBlog(Blog $blog, array $updates): Blog
    {
        $metaKeys = $blog->getMetaKeys();

        $metaUpdates = []; // metadata
        $realUpdates = []; // real columns
        $updatables = [
            'icon_url', 'featured_image_url',
            'subdomain', 'hosting_at', 'hosting_domain',
            'hosting_url',
        ];

        foreach ($updates as $key => $value) {
            if (in_array($key, $metaKeys)) {
                $metaUpdates[$key] = $value;
            } elseif (in_array($key, $updatables)) {
                $realUpdates[$key] = $value;
            }
        }

        if ($metaUpdates !== []) {
            $blog->setMeta($metaUpdates);
        }

        if ($realUpdates !== []) {
            $blog->update($realUpdates);
        }

        return $blog;
    }

    public static function createBlogVariant($blog, int $languageId): void
    {
        $language = Language::where('id', '=', $languageId)
        ->value('is_primary');

        if ($language == 0) {
            $userVariantCheck = BlogVariant::where('blog_id', '=', $blog->id)
            ->where('language_id', '=', $languageId)
            ->first();

            if ($userVariantCheck == null) {
                BlogVariant::create([
                    'blog_id' => $blog->id,
                    'language_id' => $languageId,
                ]);
            }
        }
    }
}
