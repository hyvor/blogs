<?php

namespace App\Api\Console\Input\Hosting;

use App\Entity\Enum\BlogHostingAt;
use App\Service\Blog\BlogService;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateHostingInput
{

    public BlogHostingAt $hosting_at;

    #[Assert\Regex(BlogService::SUBDOMAIN_REGEX)]
    public ?string $subdomain = null;

    #[Assert\Url]
    public ?string $hosting_url = null;

}
