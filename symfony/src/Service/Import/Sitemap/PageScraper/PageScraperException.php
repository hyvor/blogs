<?php

namespace App\Service\Import\Sitemap\PageScraper;

use App\Service\Import\Sitemap\PageScraper\Enum\PageScrapeError;
use Exception;
use Throwable;

class PageScraperException extends Exception
{
    public function __construct(
        public PageScrapeError $error,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
