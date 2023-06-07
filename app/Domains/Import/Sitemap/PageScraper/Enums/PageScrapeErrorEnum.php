<?php declare(strict_types=1);

namespace App\Domains\Import\Sitemap\PageScraper\Enums;

enum PageScrapeErrorEnum : string
{
    case CANNOT_FETCH = 'cannot_fetch';
    case CANNOT_GET_SLUG = 'canno_get_slug';
}