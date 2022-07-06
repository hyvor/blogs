<?php

namespace App\Domains\Delivery\Processors\Sitemap;

use App\Models\Post;

class PostEntry
{

    public function __construct(private Post $post) {}



}