<?php

namespace App\Api\Console\Input\UrlData;

use Symfony\Component\Validator\Constraints as Assert;

class GetUrlDataInput
{
    #[Assert\NotBlank]
    #[Assert\Url]
    public string $url;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['link', 'embed'])]
    public string $type;
}
