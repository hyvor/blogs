<?php

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\Theme\ThemeRepository;
use App\Models\Blog;

class ThemeFiller implements FillerInterface
{

    public function __construct(private Blog $blog)
    {}

    public function fill()
    {

        $theme = $this->blog->type === BlogTypeEnum::DEV ? 'blank' : 'default';
        ThemeFilesRepository::copyThemeToBlog($this->blog, $theme);

    }

}