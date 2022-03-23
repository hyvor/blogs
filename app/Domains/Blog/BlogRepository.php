<?php

namespace App\Domains\Blog;

use App\Data\Enums\BlogTypeEnum;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\User;

class BlogRepository
{

    public static function createBlog(
        int $userId, 
        string $name, string $subdomain, 
        BlogTypeEnum $type = BlogTypeEnum::NORMAL    
    ) : User {

        $blog = self::getBlogBySubdomain($subdomain);

        if ($blog) {
            throw new TrustedException('This subdomain is already taken, please choose a different subdomain', 
                TrustedException::ERROR_BAD_REQUEST
            );
        }

        // create the blog
        $blog = Blog::create([
            'user_id' => $userId,
            'name' => $name,
            'subdomain' => $subdomain,
            'type' => $type->value
        ]);

        ['user' => $user] = FillNewBlog::fill($blog);

        return $user;

    }

    public static function getBlogById(int $id) : ?Blog {
        return Blog::find($id);
    }

    public static function getBlogBySubdomain(string $subdomain) : ?Blog {
        return Blog::where('subdomain', $subdomain)->first();
    }

    public static function getBlogByCustomDomain(string $customDomain) : ?Blog {
        return Blog::where('hosting_domain', $customDomain)->first();
    }

}
