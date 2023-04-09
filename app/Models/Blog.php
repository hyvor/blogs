<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\BlogBillingTypeEnum;
use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\BlogIntegrationEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Models\Concerns\Countable;
use Carbon\Carbon;
use Hyvor\JsonMeta\Definer;
use Hyvor\JsonMeta\Metable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Blog extends Model
{
    use HasFactory;
    use Countable;
    use Metable;

    protected $casts = [
        'is_blocked' => 'bool',
        'is_activated' => 'bool',
        'type' => BlogTypeEnum::class,
        'billing_type' => BlogBillingTypeEnum::class,
        'integration' => BlogIntegrationEnum::class,
        'hosting_at' => BlogHostingAtEnum::class,
        'trial_ends_at' => 'datetime'
    ];

    // meta
    protected function metaDefinition(Definer $definer) : void
    {
        $definer->add('embeddable')->default(false);
        $definer->add('embedding_domains')->default(null);

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
            <<<'TEXT'
        User-agent: *
        Sitemap: {{ _blog.base_url }}/sitemap.xml
        Disallow: /p/
        TEXT
        );
        $definer->add('seo_external_links_follow')->default('follow');


        $definer->add('comments_code')->default(null);
        $definer->add('newsletter_code')->default(null);

        $definer->add('color_modes')->default('both');
        $definer->add('color_mode_default')->default('os');

        $definer->add('syntax_on')->default(true);
        $definer->add('syntax_line_numbers')->default(true);
        $definer->add('syntax_theme')->default(null);

        $definer->add('flashload')->default(true);
    }

    /**
     * @return string[]
     */
    protected function countsDefinition()
    {
        return [
            'users',
            'posts',
            'posts_draft',
            'posts_scheduled',
            'posts_featured',
            'media',
        ];
    }

    /** @var array<mixed> */
    protected $with = [
        'variants',
    ];

    /**
     * @return HasMany<BlogVariant>
     */
    public function variants()
    {
        return $this->hasMany(BlogVariant::class);
    }

    /**
     * @return HasMany<Post>
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return HasMany<User>
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Tag>
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * @return HasMany<Route>
     */
    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    /**
     * @return HasMany<Language>
     */
    public function languages()
    {
        return $this->hasMany(Language::class)->orderBy('id', 'ASC');
    }

    /**
     * @return HasMany<Redirect>
     */
    public function redirects()
    {
        return $this->hasMany(Redirect::class);
    }

    /**
     * @return HasMany<Navigation>
     */
    public function navigations()
    {
        return $this->hasMany(Navigation::class)->orderBy('sort', 'ASC');
    }

    /**
     * @return HasMany<Webhook>
     */
    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }

    /**
     * @return HasMany<ThemeFile>
     */
    public function themeFiles()
    {
        return $this->hasMany(ThemeFile::class);
    }

    /**
     * @return HasMany<Media>
     */
    public function medias()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * @return HasMany<Subscription>
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->orderBy('id', 'DESC');
    }

}
