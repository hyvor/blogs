<?php

namespace App\Domains\Blog;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Support\Facades\App;

use Carbon\Carbon;

class BlogRepository
{
    public static function getDomain(Blog $blog)
    {
        if ($blog->hosted_at === 'subdomain') {
            $deliveryDomain = config('blogs.domain_delivery');
            $domain = "$blog->subdomain.$deliveryDomain";
        } elseif ($blog->hosted_at === 'blog->customdomain') {
            $domain = $blog->custom_domain;
        } else {
            $domain = $blog->subdirectory;
        }
        return $domain;
    }

    public static function getFullUrlFromSlug(Blog $blog, ?string $slug)
    {
        if (is_null($slug)) {
            $slug = '';
        }

        $slug = trim($slug, '/');

        $domain = self::getDomain($blog);
        
        $protocol = App::environment('local') ? 'http://' : 'https://';

        return $protocol . $domain . ($slug ? '/' . $slug : '');
    }

    public static function getBlog( int $blogId){
        return Blog::where('id','=', $blogId)
            ->get();
    }

    public static function updateBlog(int $blogId,  array $updates){ 
        // $blogUpdate = Blog::find($blogId);
        // $blogUpdate->custom_head=$codeHead;
        // $blogUpdate->custom_footer=$codeFooter;
        // $blogUpdate->save();  

        $blog = Blog::find($blogId);

        if (array_key_exists('subdomain', $updates)) {
            $blog->subdomain = $updates['subdomain'];
        }
        if (array_key_exists('name', $updates)) {
            $blog->name = $updates['name'];
        }
        if (array_key_exists('description', $updates)) {
            $blog->description = $updates['description'];
        }
        if (array_key_exists('icon', $updates)) {
            $blog->icon = $updates['icon'];
        }
        if (array_key_exists('featured_image', $updates)) {
            $blog->featured_image = $updates['featured_image'];
        }
        if (array_key_exists('hosted_at', $updates)) {
            $blog->hosted_at = $updates['hosted_at'];
        }
        if (array_key_exists('custom_domain', $updates)) {
            $blog->custom_domain = $updates['custom_domain'];
        }
        if (array_key_exists('subdirectory', $updates)) {
            $blog->subdirectory = $updates['subdirectory'];
        }
        if (array_key_exists('social_facebook', $updates)) {
            $blog->social_facebook = $updates['social_facebook'];
        }

        if (array_key_exists('social_twitter', $updates)) {
            $blog->social_twitter = $updates['social_twitter'];
        }
        if (array_key_exists('social_linkedin', $updates)) {
            $blog->social_linkedin = $updates['social_linkedin'];
        }
        if (array_key_exists('social_youtube', $updates)) {
            $blog->social_youtube = $updates['social_youtube'];
        }
        if (array_key_exists('social_instagram', $updates)) {
            $blog->social_instagram = $updates['social_instagram'];
        }
        if (array_key_exists('social_github', $updates)) {
            $blog->social_github = $updates['social_github'];
        }
        if (array_key_exists('custom_head', $updates)) {
            $blog->custom_head = $updates['custom_head'];
        }
        if (array_key_exists('custom_footer', $updates)) {
            $blog->custom_footer = $updates['custom_footer'];
        }

        if (array_key_exists('edited_at', $updates)) {
            $blog->edited_at = $updates['edited_at'];
        }
        if (array_key_exists('posts_count', $updates)) {
            $blog->posts_count = $updates['posts_count'];
        }
        if (array_key_exists('users_count', $updates)) {
            $blog->users_count = $updates['users_count'];
        }

        $blog->save();
        return $blog;

    }
}
