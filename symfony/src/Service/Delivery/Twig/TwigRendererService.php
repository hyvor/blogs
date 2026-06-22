<?php

namespace App\Service\Delivery\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

class TwigRendererService
{
    public function __construct(
        private TwigExtensions $twigExtensions,
        #[Autowire('%kernel.environment%')]
        private string $env,
    ) {}

    /**
     * @param array<string, mixed> $vars
     */
    public function renderString(string $template, array $vars): string
    {
        $loader = new ArrayLoader(['index.twig' => $template]);
        $twig = $this->buildEnvironment($loader);
        return $twig->render('index.twig', $vars);
    }

    /**
     * @param array<string, string> $files
     * @param array<string, mixed> $vars
     */
    public function renderFromFiles(array $files, array $vars, string $fileName): string
    {
        $loader = new ArrayLoader($files);
        $twig = $this->buildEnvironment($loader);
        return $twig->render($fileName, $vars);
    }

    private function buildEnvironment(ArrayLoader $loader): Environment
    {
        $twig = new Environment($loader, ['cache' => false]);

        // hb-defined filters and functions
        $twig->addExtension($this->twigExtensions);
        
        // for template_from_string
        $twig->addExtension(new StringLoaderExtension());

        // string filters
        $twig->addExtension(new StringExtension());

        // format_datetime https://twig.symfony.com/doc/3.x/filters/format_datetime.html
        $twig->addExtension(new IntlExtension());

        if ($this->env === 'dev') {
            $twig->addExtension(new DebugExtension());
        }
            
        return $twig;
    }
}
