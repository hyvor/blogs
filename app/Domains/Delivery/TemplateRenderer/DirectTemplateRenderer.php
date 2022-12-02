<?php

namespace App\Domains\Delivery\TemplateRenderer;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use App\Models\Language;

class DirectTemplateRenderer
{

    use TemplateRendererTrait;

    public function __construct(
        private readonly Blog $blog,
        private readonly Language $language,
        private readonly string $templateName
    )
    {}

    public function render()
    {

        $vars =$this->getDefaultVariables($this->blog, $this->language);
        $vars = json_decode(json_encode($vars), true);

        $allTemplates = ThemeFilesRepository::getFilesInFolder($this->blog,ThemeFileFolderEnum::TEMPLATES);

        $loaderArray = [];
        foreach ($allTemplates as $file) {
            $loaderArray[$file->name] = $file->content;
        }

        return TwigRenderer::renderFromFiles($loaderArray, $vars, $this->templateName);

    }

}