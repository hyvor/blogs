<?php

namespace App\Service\Ai\Translate\Dto;

class TranslatedResponse
{

    public ?string $title = null;
    public ?string $description = null;
    public ?string $slug = null;

    /** @var TranslatedContentResponse[] */
    public array $content = [];

    public function getTitle(): ?string
    {
        return empty($this->title) ? null : $this->title;
    }

    public function getDescription(): ?string
    {
        return empty($this->description) ? null : $this->description;
    }

    public function getSlug(): ?string
    {
        return empty($this->slug) ? null : $this->slug;
    }

}
