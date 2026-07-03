<?php

namespace App\Api\Console\Input\Hosting;

use App\Entity\Enum\BlogHostingAt;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateHostingInput
{

    public BlogHostingAt $hosting_at;

    #[Assert\Url]
    public ?string $hosting_url = null;

}
