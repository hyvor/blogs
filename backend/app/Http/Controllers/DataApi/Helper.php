<?php declare(strict_types=1);

namespace App\Http\Controllers\DataApi;

use App\Domains\Language\LanguageRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Language;

class Helper
{
    public const DEFAULT_LIMIT = 25;

    public const MAX_LIMIT = 250;

    public const DEFAULT_PAGE = 1;

    public static function getLanguage(Blog $blog, ?string $code): Language
    {
        if (is_null($code)) {
            return LanguageRepository::getPrimaryLanguage($blog);
        } else {
            $language = LanguageRepository::getLanguageByCode($blog, $code);

            if (! $language) {
                throw new TrustedException('Language not found', TrustedException::ERROR_UNPROCESSABLE);
            }

            return $language;
        }
    }

    /**
     * Returns an array of order bys
     *
     * @param array<mixed> $allowed
     * @return array<mixed>
     */
    public static function getSort(?string $sort, array $allowed): array
    {
        if (! $sort) {
            return [
                // reset gives the first array element
                [reset($allowed), 'DESC'],
            ];
        }

        $ret = [];
        $orderBys = explode(',', $sort);

        foreach ($orderBys as $one) {
            $one = trim($one);
            $split = preg_split('/\s+/', $one);
            $orderBy = $split[0] ?? '';
            $orderMethod = strtoupper($split[1] ?? 'desc');

            if (! array_key_exists($orderBy, $allowed)) {
                throw new TrustedException("Sort by $orderBy not supported", TrustedException::ERROR_UNPROCESSABLE);
            }

            if (! in_array($orderMethod, ['ASC', 'DESC'])) {
                throw new TrustedException(
                    "Sort method $orderMethod not supported",
                    TrustedException::ERROR_UNPROCESSABLE
                );
            }

            $ret[] = [$allowed[$orderBy], $orderMethod];
        }

        return $ret;
    }

    /**
     * Default = 25
     * Max = 250
     */
    public static function getLimit(?int $limit): int
    {
        return min($limit ?? self::DEFAULT_LIMIT, self::MAX_LIMIT);
    }

    public static function getPage(?int $page): int
    {
        return $page ?? self::DEFAULT_PAGE;
    }

    public static function getOffset(int $page, int $limit): int
    {
        return ($page - 1) * $limit;
    }
}
