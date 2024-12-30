<?php

namespace App\Domains\UrlData;

use App\Data\Enums\UrlDataFetchTypeEnum;
use Hyvor\Internal\InternalApi\ComponentType;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\InternalApi\InternalApiMethod;

class UrlDataRepository
{

    /**
     * @return array<mixed>
     * for returns
     * see [Link](https://github.com/hyvor/unfold/blob/main/src/Unfolded/UnfoldedLink.php)
     * see [Embed](https://github.com/hyvor/unfold/blob/main/src/Unfolded/UnfoldedEmbed.php)
     */
    public static function fetch(string $url, UrlDataFetchTypeEnum $fetchType): array
    {

        try {
            return InternalApi::call(
                ComponentType::CORE,
                InternalApiMethod::GET,
                '/unfold/unfold',
                [
                    'url' => $url,
                    'type' => $fetchType->value
                ]
            );
        } catch (InternalApiCallFailedException) {
            throw new UrlDataException('Unable to fetch data');
        }

    }

}
