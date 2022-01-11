<?php
namespace App\Domains\Blog;

use App\Models\Blog;

interface BlogRepositoryInterface {

    public function bySubdomain(string $subdomain, array $selectColumns = null) : Blog;
    public function byId(int $blogId, array $selectColumns = null) : Blog;

    /**
     * Gets URL of the blog or a page (slug sent in)
     */
    public function getURL(?string $slug) : string;

}