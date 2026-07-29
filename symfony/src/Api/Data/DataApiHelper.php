<?php

namespace App\Api\Data;

use App\Entity\Blog;
use App\Entity\Language;
use App\Service\Language\LanguageService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DataApiHelper
{
    public const DEFAULT_LIMIT = 25;
    public const MAX_LIMIT = 250;
    public const DEFAULT_PAGE = 1;

    public function __construct(private LanguageService $languageService) {}

    /**
     * Get language from blog by code, or primary language if code is null.
     * Throws 422 if not found.
     */
    public function getLanguage(Blog $blog, ?string $code): Language
    {
        if ($code === null) {
            return $this->languageService->getPrimaryLanguage($blog);
        }

        $language = $this->languageService->getLanguageByCode($blog, $code);
        if ($language === null) {
            throw new UnprocessableEntityHttpException('Language not found');
        }

        return $language;
    }

    /**
     * Parse and validate sort string. Returns array of [column, direction] pairs.
     * Throws 422 for invalid sort.
     *
     * @param array<string, string> $allowed Map of sort key => DQL column
     * @return array<array{0: string, 1: string}>
     */
    public function getSort(?string $sort, array $allowed): array
    {
        if (!$sort) {
            // Default: first allowed key DESC
            $firstColumn = reset($allowed);
            return [[$firstColumn !== false ? $firstColumn : '', 'DESC']];
        }

        $result = [];
        $orderBys = explode(',', $sort);

        foreach ($orderBys as $one) {
            $one = trim($one);
            $split = preg_split('/\s+/', $one);
            $orderBy = $split[0] ?? '';
            $orderMethod = strtoupper($split[1] ?? 'DESC');

            if (!array_key_exists($orderBy, $allowed)) {
                throw new UnprocessableEntityHttpException("Sort by $orderBy not supported");
            }

            if (!in_array($orderMethod, ['ASC', 'DESC'], true)) {
                throw new UnprocessableEntityHttpException("Sort method $orderMethod not supported");
            }

            $result[] = [$allowed[$orderBy], $orderMethod];
        }

        return $result;
    }

    /**
     * Returns limit capped at MAX_LIMIT. Throws 422 if limit < 1.
     */
    public function getLimit(?int $limit): int
    {
        if ($limit !== null && $limit < 1) {
            throw new UnprocessableEntityHttpException('Limit must be at least 1');
        }
        return min($limit ?? self::DEFAULT_LIMIT, self::MAX_LIMIT);
    }

    /**
     * Returns page (default 1). Throws 422 if page < 1.
     */
    public function getPage(?int $page): int
    {
        if ($page !== null && $page < 1) {
            throw new UnprocessableEntityHttpException('Page must be at least 1');
        }
        return $page ?? self::DEFAULT_PAGE;
    }

    public function getOffset(int $page, int $limit): int
    {
        return ($page - 1) * $limit;
    }
}
