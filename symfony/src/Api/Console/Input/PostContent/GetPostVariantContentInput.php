<?php

namespace App\Api\Console\Input\PostContent;

use App\Service\Post\Content\PostContentFormat;
use Symfony\Component\Validator\Constraints as Assert;

class GetPostVariantContentInput
{
    /**
     * Language code of the variant. Defaults to the primary language.
     */
    #[Assert\Length(max: 16)]
    public ?string $language_code = null;

    public PostContentFormat $format = PostContentFormat::MARKDOWN;
}
