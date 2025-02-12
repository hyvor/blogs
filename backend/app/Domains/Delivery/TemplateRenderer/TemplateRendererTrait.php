<?php declare(strict_types=1);

namespace App\Domains\Delivery\TemplateRenderer;

// shared between TemplateRenderer and DirectTemplateRenderer
use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DataAPI\LanguageObject;
use App\Domains\App\DomainService;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use App\Models\Language;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Twig\Error\Error;

trait TemplateRendererTrait
{

    /**
     * @var array<string, mixed>|null
     */
    private ?array $config = null;

    private function setConfig(Blog $blog): void
    {
        $configFile = ThemeFilesRepository::getFile($blog, 'config.yaml');

        if (!$configFile) {
            $this->config = [];
            return;
        }

        try {
            $this->config = Yaml::parse($configFile->content ?? '') ?? [];
        } catch (ParseException) {
            throw new Error('Unable to parse config.yaml');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultVariables(Blog $blog, Language $language): array
    {

        if ($this->config === null) {
            $this->setConfig($blog);
        }

        $blogObject = new BlogObject($blog, $language);

        return [
            // HB-specific
            '__domain' => DomainService::getAppDomainWithPort(),

            // vars for all routes
            '_blog' => $blogObject,
            '_config' => $this->config,
            '_lang' => new LanguageObject($language),

            // placeholders
            '_head' => $this->getHeadCode(),
            '_foot' => $this->getFootCode(),
        ];

    }

    private function getHeadCode(): string
    {
        return (string) file_get_contents(resource_path('twig/_head.twig'));
    }

    private function getFootCode(): string
    {
        return (string) file_get_contents(resource_path('twig/_foot.twig'));
    }

}