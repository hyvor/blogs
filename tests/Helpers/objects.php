<?php

use App\Data\Objects\DataAPI\BlogObject;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Models\Blog;

function getBlogObject($updates = [], Blog $blog = null): BlogObject
{

    $blog ??= blogWithLanguageAndRoutes();

    $obj = new BlogObject($blog, $blog->languages[0]);
    foreach ($updates as $key => $value) {
        $obj->$key = $value;
    }

    return $obj;
}