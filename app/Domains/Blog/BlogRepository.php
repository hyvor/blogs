<?php

namespace App\Domains\Blog;

use App\Models\Blog;

class BlogRepository
{

    public static function createBlog(Blog $blog) {

        

    }

    public static function getBlogBySubdomain(string $subdomain) : ?Blog {
        return Blog::where('subdomain', $subdomain)->first();
    }

    public static function getBlogByCustomDomain(string $customDomain) : ?Blog {
        return Blog::where('hosting_domain', $customDomain)->first();
    }

}
