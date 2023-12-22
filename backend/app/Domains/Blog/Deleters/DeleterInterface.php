<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;

interface DeleterInterface
{
    public function __construct(Blog $blog);

    public function delete();
}
