<?php

namespace App\Api\Console\Input\PostContent;

use App\Service\Post\Content\PostContentFormat;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostVariantContentInput
{
    /**
     * Language code of the variant. Defaults to the primary language.
     */
    #[Assert\Length(max: 16)]
    public ?string $language_code = null;

    public PostContentFormat $format = PostContentFormat::MARKDOWN;

    #[Assert\NotNull]
    public string $content;

    /**
     * The `version` returned by the GET endpoint. If given and the document has changed
     * since, the request fails with 409. If omitted, the content is overwritten.
     */
    #[Assert\PositiveOrZero]
    public ?int $version = null;
}
