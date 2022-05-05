<?php

namespace App\Domains\Blog\Fillers;

use App\Models\Blog;

interface FillerInterface
{
    public function __construct(Blog $blog);
    public function fill();
}
