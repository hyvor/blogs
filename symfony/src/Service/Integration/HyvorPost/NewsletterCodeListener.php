<?php

namespace App\Service\Integration\HyvorPost;

use App\Service\Delivery\TemplateRenderer\TemplateRenderingEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class NewsletterCodeListener
{

    public function __construct(private HyvorPostService $hyvorPostService) {}

    public function __invoke(TemplateRenderingEvent $event)
    {
        $hp = $this->hyvorPostService->getHyvorPostOfBlog($event->blog);

        if (!$hp) {
            return;
        }

        $variables = $event->getVariables();

        $code = HyvorPostService::getEmbedCode($hp);
        $variables['_newsletter'] .= $code;

        $event->setVariables($variables);
    }

}
