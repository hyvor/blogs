<?php

namespace App\Models;

use App\Models\Concerns\Countable;
use App\Models\Concerns\Metable;
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

    /**
     * Meta
     */
    protected $metableDefinition = [

        // social media
        'social_facebook' => ['string|null', null],
        'social_twitter' => ['string|null', null],
        'social_linkedin' => ['string|null', null],
        'social_youtube' => ['string|null', null],
        'social_instagram' => ['string|null', null],
        'social_github' => ['string|null', null],

        // code
        'code_head' => ['string|null', null],
        'code_foot' => ['string|null', null],

        // seo
        'seo_indexing' => ['bool', true],
        'seo_robots' => ['string|null', null],
        'seo_follow_external_links' => ['bool', false],

        // comments
        'comments_type' => ['enum:ht,other', 'ht'],
        'comments_ht_website_id' => ['int|null', null],
        'comments_ht_api_key' => ['string|null', null],
        'comments_code' => ['string|null', null],

        // newsletter
        'newsletter_code' => ['string|null', null],

        // color mode
        'color_mode_allowed' => ['enum:light,dark,both', 'both'],
        'color_mode_default' => ['enum:light,dark,os', 'os'],

        // syntax highlighting
        'syntax_on' => ['bool', true],
        'syntax_line_numbers' => ['bool', true],
        'syntax_theme' => ['string|null', null],

    ];

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
