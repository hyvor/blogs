<?php

namespace App\Service\Delivery\TemplateRenderer;

use App\Entity\Blog;

class TemplateRenderingEvent
{

    public function __construct(
        public readonly Blog $blog,
        /**
         * @var array<string, mixed>
         */
        private array $variables
    ) {}

    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

}
