<?php

namespace App\Models;

use App\Models\Concerns\Countable;
use Hyvor\JsonMeta\Definer;
use Hyvor\JsonMeta\Metable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Paddle\Billable;

class Blog extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Billable;
    use Countable;
    use Metable;

    // meta
    protected function metaDefinition(Definer $definer)
    {
        
        $definer->add('social_facebook')->type('string|null')->default(null);
        $definer->add('social_twitter')->type('string|null')->default(null);
        $definer->add('social_linkedin')->type('string|null')->default(null);
        $definer->add('social_youtube')->type('string|null')->default(null);
        $definer->add('social_instagram')->type('string|null')->default(null);
        $definer->add('social_github')->type('string|null')->default(null);

        $definer->add('code_head')->type('string|null')->default(null);
        $definer->add('code_foot')->type('string|null')->default(null);
        
        $definer->add('seo_indexing')->type('bool')->default(true);
        $definer->add('seo_robots_txt')->type('string|null')->default(<<<TEXT
        User-agent: *
        Sitemap: {{ _blog.url }}/sitemap.xml
        Disallow: /p/
        TEXT
        );
        $definer->add('seo_external_links_follow')->type('enum:nofollow,follow')->default('follow');

        $definer->add('comments_type')->type('enum:ht,other')->default('ht');
        $definer->add('comments_ht_website_id')->type('int|null')->default(null);
        $definer->add('comments_ht_api_key')->type('string|null')->default(null);
        $definer->add('comments_code')->type('string|null')->default(null);

        $definer->add('newsletter_code')->type('string|null')->default(null);
        
        $definer->add('color_modes')->type('enum:light,dark,both')->default('both');
        $definer->add('color_mode_default')->type('enum:light,dark,os')->default('os');

        $definer->add('syntax_on')->type('bool')->default(true);
        $definer->add('syntax_line_numbers')->type('bool')->default(true);
        $definer->add('syntax_theme')->type('string|null')->default(null);


    }

    protected $with = [
        'variants'
    ];

    public function variants()
    {
        return $this->hasMany(BlogVariant::class);
    }

    /**
     * Get Media of the blog
     */
    public function mediaIcon()
    {
        return $this->hasOne(Media::class, 'icon_id');
    }

    public function mediaPicture()
    {
        return $this->hasOne(Media::class, 'featured_image_id');
    }

    /**
     * Get Posts of the blog
     */
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

    /**
     * Get routes of the blog
     */
    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get languages of the blog
     */
    public function languages()
    {
        return $this->hasMany(Language::class);
    }

    /**
     * Redirects
     */
    public function redirects()
    {
        return $this->hasMany(Redirect::class);
    }

    /**
     * Webhooks
     */
    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }


    /**
     * Theme files
     */
    public function themeFiles()
    {
        return $this->morphMany(ThemeFile::class, 'themable');
    }

}
