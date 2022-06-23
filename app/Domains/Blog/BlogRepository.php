<?php

namespace App\Domains\Blog;

use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\NavigationFiller;
use App\Domains\Blog\Fillers\PostFiller;
use App\Domains\Blog\Fillers\RouteFiller;
use App\Domains\Blog\Fillers\TagFiller;
use App\Domains\Blog\Fillers\UserFiller;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Language;

class BlogRepository
{
    public static function createBlog(
        ?int $userId,
        string $name,
        string $subdomain,
        BlogTypeEnum $type = BlogTypeEnum::DEFAULT
    ): Blog {
        $blog = Blog::create([
            'hyvor_user_id' => $userId,
            'subdomain' => $subdomain,
            'type' => $type->value,
        ]);

        // start trial
        $blog->createAsCustomer([
            'trial_ends_at' => now()->addDays(config('limits.trial_days')),
        ]);

        // fill data
        $fillers = [
            LanguageFiller::class,
            UserFiller::class,
            TagFiller::class,
            PostFiller::class,
            RouteFiller::class,
            NavigationFiller::class,
        ];

        foreach ($fillers as $filler) {
            (new $filler($blog))->fill();
        }

        BlogVariant::create([
            'blog_id' => $blog->id,
            'language_id' => $blog->languages[0]->id,
            'name' => $name,
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
     * @param array $updates
     * @return Blog
     */
    public static function updateBlog(Blog $blog, array $updates): Blog
    {
        $metaKeys = $blog->getMetaKeys();

        $metaUpdates = []; // metadata
        $realUpdates = []; // real columns
        $updatables = [
            'subdomain',
            'hosting_at',
            'hosting_domain',
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

        if (count($realUpdates)) {

            // free up custom domains
            if (
                array_key_exists('hosting_at', $realUpdates) &&
                $realUpdates['hosting_at'] !== BlogHostingAtEnum::DOMAIN->value
            ) {
                $realUpdates['hosting_domain'] = null;
            }

            $blog->update($realUpdates);
        }

        return $blog;
    }

    public static function createBlogVariant(Blog $blog, Language $language): BlogVariant
    {
        $variant = BlogVariant::where('blog_id', $blog->id)
            ->where('language_id', $language->id)
            ->first();

        if ($variant) {
            throw new TrustedException('Variant already there');
        }

        return BlogVariant::create([
            'blog_id' => $blog->id,
            'language_id' => $language->id,
        ]);
    }

    /**
     * @param Blog $blog
     * @param Language $language
     * @param array{name?: string, description?: string|null} $updates
     * @return BlogVariant
     */
    public static function updateBlogVariant(Blog $blog, Language $language, array $updates)
    {
        $variant = BlogVariant::where('blog_id', $blog->id)
            ->where('language_id', $language->id)
            ->first();

        if (array_key_exists('name', $updates)) {
            $variant->name = $updates['name'];
        }
        if (array_key_exists('description', $updates)) {
            $variant->description = $updates['description'];
        }

        $variant->save();

        return $variant;
    }
}
