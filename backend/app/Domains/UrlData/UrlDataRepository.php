<?php

namespace App\Domains\UrlData;

use App\Data\Enums\UrlDataFetchTypeEnum;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InternalApi;

class UrlDataRepository
{
    public function __construct(private readonly InternalApi $internalApi) {}

    /**
     * @return array<mixed>
     * for returns
     * see [Link](https://github.com/hyvor/unfold/blob/main/src/Unfolded/UnfoldedLink.php)
     * see [Embed](https://github.com/hyvor/unfold/blob/main/src/Unfolded/UnfoldedEmbed.php)
     */
    public function fetch(string $url, UrlDataFetchTypeEnum $fetchType): array
    {
        try {
            return $this->internalApi->call(Component::CORE, "/unfold/unfold", [
                "url" => $url,
                "type" => $fetchType->value,
            ]);
        } catch (InternalApiCallFailedException) {
            throw new UrlDataException("Unable to fetch data");
        }
    }
}
