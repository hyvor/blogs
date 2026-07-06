<?php

namespace App\Api\Console\Input\Media;

use Symfony\Component\Validator\Constraints as Assert;

class UploadMediaFromUrlInput
{
    #[Assert\NotBlank]
    #[Assert\Url]
    public string $url;

    public ?int $post_id = null;
}
