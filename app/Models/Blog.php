<?php

namespace App\Models;

use App\Models\Concerns\Countable;
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
        return $this->hasMany(BlogThemeFile::class);
    }

}
