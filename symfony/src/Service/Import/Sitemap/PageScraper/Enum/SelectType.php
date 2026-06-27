<?php

namespace App\Service\Import\Sitemap\PageScraper\Enum;

enum SelectType: string
{
    case META_TAG = 'meta_tag';
    case CSS_SELECTOR = 'css_selector';
}
