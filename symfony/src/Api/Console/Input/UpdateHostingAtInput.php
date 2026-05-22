<?php

namespace App\Api\Console\Input;

use App\Entity\Enum\BlogHostingAt;

class UpdateHostingAtInput
{

    public BlogHostingAt $hosting_at;
    public ?string $hosting_url;
}