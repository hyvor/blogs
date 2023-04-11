<?php

use App\Models\Blog;
use App\Models\Language;


function addLanguage(Blog $blog, bool $isPrimary = false) : Language {
    return Language::factory()->create([
        'blog_id' => $blog,
        'is_primary' => $isPrimary,
    ]);
}
function addPrimaryLanguage(Blog $blog) {
    return addLanguage($blog, true);
}