<?php

namespace App\Api\Console\Input\Blog\Webhook;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateWebhookInput
{
    #[Assert\Url]
    public ?string $url = null;

    /** @var string[]|null $events */
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
    public ?array $events = null;
}
