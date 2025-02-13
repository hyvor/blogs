<?php

namespace App\Domains\Delivery\TemplateRenderer;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DeliveryAPI\MetaObject;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use App\Models\Language;

class DirectTemplateRenderer
{

    use TemplateRendererTrait;

    public function __construct(
        private readonly Blog $blog,
        private readonly Language $language,
        private readonly string $templateName,
        private readonly string $path,
    )
    {}

    public function render() : string
    {

        $vars = $this->getDefaultVariables($this->blog, $this->language);
        $url = PermalinkRepository::getFullUrlFromPath($this->blog, $this->path);
        $vars['_meta'] = new MetaObject(
            title: null,
            description: null,
            featured_image: null,
            url: $url,
            canonical_url: $url,
        );

        $vars = json_decode((string) json_encode($vars), true);

        $allTemplates = ThemeFilesRepository::getFilesInFolder($this->blog,ThemeFileFolderEnum::TEMPLATES);

        $loaderArray = [];
        foreach ($allTemplates as $file) {
            $loaderArray[$file->name] = $file->content ?? '';
        }

        return TwigRenderer::renderFromFiles($loaderArray, $vars, $this->templateName);

    }

}