<?php

namespace App\Domains\Blog;

use App\Domains\Shared\UniqueSlugGeneratorTrait;
use App\Models\Blog;

final class UniqueSubdomainGenerator
{
    use UniqueSlugGeneratorTrait;

    public function exists(string $slug): bool
    {
        return Blog::where('subdomain', $slug)->exists();
    }
}
