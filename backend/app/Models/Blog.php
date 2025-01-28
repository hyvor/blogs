<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Domains\Route\PermalinkRepository;
use App\Models\Concerns\Countable;
use Carbon\Carbon;
use Database\Factories\BlogFactory;
use Hyvor\JsonMeta\Definer;
use Hyvor\JsonMeta\Metable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $ip
 * @property bool $is_blocked
 * @property ?Carbon $blocked_at
 * @property ?int $hyvor_user_id
 * @property ?int $theme_version_id
 * @property string $subdomain
 * @property Carbon $trial_ends_at
 * @property BlogTypeEnum $type
 * @property BlogHostingAtEnum $hosting_at
 * @property ?string $hosting_domain
 * @property ?string $hosting_url
 * @property bool $hosting_redirect_subdomain
 */
class Blog extends Model
{

    /**
     * @use HasFactory<BlogFactory>
     */
    use HasFactory;
    use Countable;
    use Metable;


    protected $casts = [
        'is_blocked' => 'bool',
        'type' => BlogTypeEnum::class,
        'hosting_at' => BlogHostingAtEnum::class,
        'hosting_redirect_subdomain' => 'bool',
        'trial_ends_at' => 'datetime'
    ];

    // meta
    protected function metaDefinition(Definer $definer) : void
    {
        $definer->add('embeddable')->default(false);
        $definer->add('embedding_domains')->default(null);

        $definer->add('logo_url')->default(null);
        $definer->add('cover_url')->default(null);
        $definer->add('icon_url')->default(null);

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
        $definer->add('seo_rich_schema')->default(true);


        $definer->add('comments_code')->default(null);
        $definer->add('newsletter_code')->default(null);

        $definer->add('color_modes')->default('both');
        $definer->add('color_mode_default')->default('os');

        $definer->add('syntax_on')->default(true);
        $definer->add('syntax_line_numbers')->default(true);
        $definer->add('syntax_theme')->default(null);

        $definer->add('heading_anchors')->default(true);

        $definer->add('flashload')->default(true);

        $definer->add('link_analysis_enabled')->default(true);
        $definer->add('link_analysis_email_report')->default('broken');

        $definer->add('hb_branding')->default(null);
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

    protected $with = [
        'variants',
    ];

    /**
     * @return HasMany<BlogVariant, $this>
     */
    public function variants()
    {
        return $this->hasMany(BlogVariant::class);
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Tag, $this>
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * @return HasMany<Route, $this>
     */
    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    /**
     * @return HasMany<Language, $this>
     */
    public function languages()
    {
        return $this->hasMany(Language::class)->orderBy('id', 'ASC');
    }

    /**
     * @return HasMany<Redirect, $this>
     */
    public function redirects()
    {
        return $this->hasMany(Redirect::class);
    }

    /**
     * @return HasMany<Navigation, $this>
     */
    public function navigations()
    {
        return $this->hasMany(Navigation::class)->orderBy('sort', 'ASC');
    }

    /**
     * @return HasMany<Webhook, $this>
     */
    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }

    /**
     * @return HasMany<ThemeFile, $this>
     */
    public function themeFiles()
    {
        return $this->hasMany(ThemeFile::class);
    }

    /**
     * @return HasMany<Media, $this>
     */
    public function medias()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->orderBy('id', 'DESC');
    }

    /**
     * @return HasMany<Export, $this>
     */
    public function exports()
    {
        return $this->hasMany(Export::class);
    }

    public function url() : string
    {
        return PermalinkRepository::getBaseUrl($this);
    }

    public function urlWithoutProtocol() : string
    {
        $url = $this->url();
        return strval(preg_replace('/^https?:\/\//', '', $url));
    }

    public function isInTrial() : bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /**
     * @return HasOne<HyvorTalkWebsite, $this>
     */
    public function hyvorTalkWebsite()
    {
        return $this->hasOne(HyvorTalkWebsite::class);
    }

    /**
     * @return HasMany<HyvorTalkGatedContentRule, $this>
     */
    public function hyvorTalkGatedContentRules()
    {
        return $this->hasMany(HyvorTalkGatedContentRule::class);
    }

}
