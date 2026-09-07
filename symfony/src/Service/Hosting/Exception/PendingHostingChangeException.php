<?php

namespace App\Service\Hosting\Exception;

use App\Entity\Blog;

class PendingHostingChangeException extends \RuntimeException
{
    public function __construct(Blog $blog)
    {
        parent::__construct("Blog {$blog->getId()} already has a pending hosting change");
    }
}
