<?php

namespace App\Api\Console\Input\Blog\Webhook;

use Symfony\Component\Validator\Constraints as Assert;

class CreateWebhookInput
{
    #[Assert\NotBlank]
    #[Assert\Url]
    public string $url;

    /** @var string[] $events */
    #[Assert\NotBlank]
    #[Assert\All(new Assert\Choice([
        'blog.updated',
        'post.created',
        'post.updated',
        'post.deleted',
        'tag.created',
        'tag.updated',
        'tag.deleted',
        'user.created',
        'user.updated',
        'user.deleted',
        'media.created',
        'media.deleted',
        'navigation.changed',
        'routes.changed',
        'languages.changed',
        'cache.single',
        'cache.templates',
        'cache.all',
    ]))]
    public array $events;
}
