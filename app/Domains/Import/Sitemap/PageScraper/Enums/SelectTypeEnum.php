<?php declare(strict_types=1);

namespace App\Domains\Import\Sitemap\PageScraper\Enums;

enum SelectTypeEnum : string
{
    case META_TAG = 'meta_tag';
    case CSS_SELECTOR = 'css_selector';
}
