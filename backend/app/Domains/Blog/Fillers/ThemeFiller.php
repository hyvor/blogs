<?php declare(strict_types=1);

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;

class ThemeFiller implements FillerInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function fill() : void
    {
        if ($this->blog->type === BlogTypeEnum::PREVIEW) {
            return;
        }

        $theme = $this->blog->type === BlogTypeEnum::DEV ? 'blank' : 'hello';
        ThemeFilesRepository::copyThemeToBlog($this->blog, $theme);
    }
}
