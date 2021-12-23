<?php
namespace App\Repositories\Blog;

use App\Models\Blog;
use App\Models\User;

class BlogRepository implements BlogRepositoryInterface {

    public function bySubdomain(string $subdomain, array $selectColumns = null) : Blog {
        $blog = Blog::where('subdomain', $subdomain);
        if ($selectColumns) {
            $blog->select($selectColumns);
        }
        return $blog->first();
    }
    public function byId(int $blogId, array $selectColumns = null) : Blog {
        $blog = Blog::where('id', $blogId);
        if ($selectColumns) {
            $blog->select($selectColumns);
        }
        return $blog->first();
    }

    public function getURL(?string $slug) : string {

        return '';
    }


}