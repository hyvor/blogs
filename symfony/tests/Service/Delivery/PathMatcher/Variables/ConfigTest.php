<?php

namespace App\Tests\Service\Delivery\PathMatcher\Variables;

use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
class ConfigTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_sets_config_variable(): void
    {
        $configYaml = <<<YAML
        name: Hyvor
        nested:
            value: Blogs
        YAML;

        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createOneFor($blog, 'config.yaml', $configYaml, null);
        ThemeFileFactory::createIndexTwig($blog, "{{ _config.name }}\n{{ _config.nested.value }}");

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame("Hyvor\nBlogs", $response->content);
    }

    public function test_config_is_empty_array_when_config_yaml_does_not_exist(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createIndexTwig($blog, '{% if _config is empty %}empty{% endif %}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame('empty', $response->content);
    }

    public function test_config_falls_back_to_empty_array_when_yaml_is_invalid(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createOneFor($blog, 'config.yaml', '@invalid:yaml:is:here', null);
        ThemeFileFactory::createIndexTwig($blog, '{{ _config.name }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(500, $response->status);
        $this->assertStringContainsString('config.yaml could not be parsed:', (string) $response->content);
        $this->assertStringContainsString('(near "@invalid:yaml:is:here")', (string) $response->content);
    }
}
