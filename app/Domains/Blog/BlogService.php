<?php

namespace App\Domains\Blog;

use App\Data\Enums\BlogBillingTypeEnum;
use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\BlogIntegrationEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\Deleters\LanguageDeleter;
use App\Domains\Blog\Deleters\MediaDeleter;
use App\Domains\Blog\Deleters\NavigationDeleter;
use App\Domains\Blog\Deleters\PostDeleter;
use App\Domains\Blog\Deleters\RedirectDeleter;
use App\Domains\Blog\Deleters\RouteDeleter;
use App\Domains\Blog\Deleters\TagDeleter;
use App\Domains\Blog\Deleters\ThemeDeleter;
use App\Domains\Blog\Deleters\UserDeleter;
use App\Domains\Blog\Events\BlogDeletedEvent;
use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Events\BlogVariantUpdatedEvent;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\NavigationFiller;
use App\Domains\Blog\Fillers\PostFiller;
use App\Domains\Blog\Fillers\RouteFiller;
use App\Domains\Blog\Fillers\TagFiller;
use App\Domains\Blog\Fillers\ThemeFiller;
use App\Domains\Blog\Fillers\UserFiller;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Language;

class BlogService
{
    public function createBlog(
        ?int $userId,
        string $name,
        string $subdomain,
        BlogTypeEnum $type = BlogTypeEnum::DEFAULT,
        BlogBillingTypeEnum $billingType = BlogBillingTypeEnum::PADDLE,
        BlogIntegrationEnum $integration = null
    ): Blog
    {
        $blog = Blog::create([
            'hyvor_user_id' => $userId,
            'subdomain' => $subdomain,
            'type' => $type,
            'billing_type' => $billingType,
            'integration' => $integration,
            'trial_ends_at' => now()->addDays(config('limits.trial_days')),
        ]);
        $blog->refresh(); // fetch default columns

        (new LanguageFiller($blog))->fill();

        /**
         * Creating the variant is important because all functions are designed assuming primary variant is there
         * Other fillers can be risky (theme copying for example)
         * So, first create the languages and variant
         * THen, we are running the other fillers.
         */
        BlogVariant::create([
            'blog_id' => $blog->id,
            'language_id' => $blog->languages[0]->id,
            'name' => $name,
        ]);

        // other fillers
        $fillers = [
            UserFiller::class,
            TagFiller::class,
            PostFiller::class,
            RouteFiller::class,
            NavigationFiller::class,
            ThemeFiller::class,
        ];

        foreach ($fillers as $filler) {
            app($filler, ['blog' => $blog])->fill();
        }

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
     * @param  Blog  $blog
     * @param  array $updates
     * @return Blog
     */
    public static function updateBlog(Blog $blog, array $updates): Blog
    {

        $blogOriginal = $blog->replicate();

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

            foreach ($realUpdates as $key => $value) {
                $blog->$key = $value;
            }
            $blog->save();
        }

        BlogUpdatedEvent::dispatch($blog, $blogOriginal);

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

    public static function activateBlog(Blog $blog) : void
    {
        $blog->update([
            'is_activated' => true
        ]);
    }

    /**
     * @param  Blog  $blog
     * @param  Language  $language
     * @param  array{name?: string, description?: string|null}  $updates
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

        BlogVariantUpdatedEvent::dispatch($variant);

        return $variant;
    }

    public function deleteBlog(Blog $blog)
    {
        $deleters = [
            LanguageDeleter::class,
            MediaDeleter::class,
            NavigationDeleter::class,
            PostDeleter::class,
            RedirectDeleter::class,
            RouteDeleter::class,
            TagDeleter::class,
            ThemeDeleter::class,
            UserDeleter::class,
        ];

        foreach ($deleters as $deleter) {
            app($deleter, ['blog' => $blog])->delete();
        }

        $blog->delete();

        BlogDeletedEvent::dispatch($blog);
    }
}
