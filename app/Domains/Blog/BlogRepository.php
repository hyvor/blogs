<?php

namespace App\Domains\Blog;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Support\Facades\App;

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
}
