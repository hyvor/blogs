<?php

namespace App\Service\Import\Sitemap\PageScraper\Enum;

enum PageScrapeError: string
{
    case CANNOT_FETCH = 'cannot_fetch';
    case CANNOT_GET_SLUG = 'cannot_get_slug';
    case CANNOT_GET_CONTENT = 'cannot_get_content';
}
