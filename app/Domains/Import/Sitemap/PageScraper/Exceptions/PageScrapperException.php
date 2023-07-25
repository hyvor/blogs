<?php declare(strict_types=1);

namespace App\Domains\Import\Sitemap\PageScraper\Exceptions;

use App\Domains\Import\Sitemap\PageScraper\Enums\PageScrapeErrorEnum;
use Exception;
use Throwable;

class PageScrapperException extends Exception
{

    public function __construct(
        public PageScrapeErrorEnum $error,
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

}