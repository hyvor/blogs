<?php

namespace App\Models;

use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Models\Concerns\Countable;
use Hyvor\JsonMeta\Definer;
use Hyvor\JsonMeta\Metable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Paddle\Billable;

/**
 * @property BlogTypeEnum type
 * @property int hyvor_user_id
 *
 * @property Collection $tags
 * @property Collection $users
 */
class Blog extends Model
{
    use HasFactory;
    use Billable;
    use Countable;
    use Metable;

    protected $casts = [
        'type' => BlogTypeEnum::class,
        'hosting_at' => BlogHostingAtEnum::class,
    ];

    // meta
    protected function metaDefinition(Definer $definer)
    {
        $definer->add('logo_url')->default(null);
        $definer->add('cover_url')->default(null);

        $definer->add('social_facebook')->default(null);
        $definer->add('social_twitter')->default(null);
        $definer->add('social_linkedin')->default(null);
        $definer->add('social_youtube')->default(null);
        $definer->add('social_tiktok')->default(null);
        $definer->add('social_instagram')->default(null);
        $definer->add('social_github')->default(null);

        $definer->add('code_head')->default(null);
        $definer->add('code_foot')->default(null);

        $definer->add('seo_indexing')->default(true);
        $definer->add('seo_robots_txt')->default(
            <<<TEXT
        User-agent: *
        Sitemap: {{ _blog.url }}/sitemap.xml
        Disallow: /p/
        TEXT
        );
        $definer->add('seo_external_links_follow')->default('follow');

        $definer->add('comments_type')->default('ht');
        $definer->add('comments_ht_website_id')->default(null);
        $definer->add('comments_ht_api_key')->default(null);
        $definer->add('comments_code')->default(null);

        $definer->add('newsletter_code')->default(null);

        $definer->add('color_modes')->default('both');
        $definer->add('color_mode_default')->default('os');

        $definer->add('syntax_on')->default(true);
        $definer->add('syntax_line_numbers')->default(true);
        $definer->add('syntax_theme')->default(null);
    }

    protected $with = [
        'variants',
    ];

    public function variants()
    {
        return $this->hasMany(BlogVariant::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    public function languages()
    {
        return $this->hasMany(Language::class);
    }

    public function redirects()
    {
        return $this->hasMany(Redirect::class);
    }

    public function navigations()
    {
        return $this->hasMany(Navigation::class)->orderBy('sort', 'ASC');
    }

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }

    public function themeFiles()
    {
        return $this->hasMany(ThemeFile::class);
    }

    public function medias()
    {
        return $this->hasMany(Media::class);
    }
}
