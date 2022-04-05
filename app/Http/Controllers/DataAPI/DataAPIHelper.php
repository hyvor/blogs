<?php
namespace App\Http\Controllers\DataAPI;

use App\Domains\Language\LanguageRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Language;

class DataAPIHelper
{

    public static function getLanguage(Blog $blog, ?string $code) : Language
    {

        if (is_null($code)) {
            return LanguageRepository::getPrimaryLanguage($blog);
        } else {
            $language = LanguageRepository::getLanguageByCode($blog, $code);

            if (!$language) {
                throw new TrustedException('Language not found', TrustedException::ERROR_BAD_REQUEST);
            }

            return $language;
        }

    }

    /**
     * Returns an array of order bys
     */
    public static function getSort(?string $sort, array $allowed) : array
    {

        if (!$sort) {
            return [
                [$allowed[0], 'DESC']
            ];
        }

        $ret = [];
        $orderBys = explode(',', $sort);

        foreach ($orderBys as $orderBy) {

            $split = explode(' ', $sort);
            $orderBy = $split[0];
            $orderMethod = strtoupper($split[1] ?? 'desc');
    
            if (!in_array($orderBy, $allowed)) {
                throw new TrustedException("Sort by $orderBy not supported", TrustedException::ERROR_BAD_REQUEST);
            }
    
            if (!in_array($orderMethod, ['ASC', 'DESC'])) {
                throw new TrustedException(
                    "Sort method $orderMethod not supported", TrustedException::ERROR_BAD_REQUEST);
                $orderMethod = 'DESC';
            }

            $ret[] = [$orderBy, $orderMethod];

        }

        return $ret;

    }

    /**
     * Default = 25
     * Max = 250
     */
    public static function getLimit(?int $limit)
    {
        return min($limit ?? 25, 250);
    }

    public static function getPage(?int $page)
    {
        return $page ?? 1;
    }

    public static function getOffset(int $page, $limit)
    {
        return ($page - 1) * $limit;
    }

}