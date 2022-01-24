<?php

namespace App\Domains\Blog;

use App\Models\Blog;
use App\Models\User;

class BlogRepository {

    static function getDomain(Blog $blog) {
        if ($blog->hosted_at === 'subdomain') {
            $deliveryDomain = config('blogs.domain_delivery');
            $domain = "$blog->subdomain.$deliveryDomain";
        } else if ($blog->hosted_at === 'blog->customdomain') {
            $domain = $blog->custom_domain;
        } else {
            $domain = $blog->subdirectory;
        }
        return $domain;
    }

    static function getFullUrlFromSlug(Blog $blog, ?string $slug) {
        if (is_null($slug))
            $slug = '';

        $slug = trim($slug, '/');

        $domain = self::getDomain($blog);

        return 'https://' . $domain . ($slug ? '/' . $slug : '');
    }
}
