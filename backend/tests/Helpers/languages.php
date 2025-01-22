<?php

use App\Models\Blog;
use App\Models\Language;


function addLanguage(Blog $blog, bool $isPrimary = false, array $attr = []) : Language {
    return Language::factory()->create(
        [
            'blog_id' => $blog,
            'is_primary' => $isPrimary,
        ] + $attr
    );
}
function addPrimaryLanguage(Blog $blog, $attr = []) : Language {
    return addLanguage($blog, true, $attr);
}
