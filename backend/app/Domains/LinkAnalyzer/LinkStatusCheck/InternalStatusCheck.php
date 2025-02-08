<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

use App\Models\Blog;

class InternalStatusCheck implements LinkStatusCheckInterface
{
    private Blog $blog;

    public function setBlog(Blog $blog): void
    {
        $this->blog = $blog;
    }

    public function check(array $urls): array
    {
        return [];
    }

}