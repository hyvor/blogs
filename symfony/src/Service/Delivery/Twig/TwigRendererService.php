<?php

namespace App\Service\Delivery\Twig;

use Twig\Environment;
use Twig\Extension\StringLoaderExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

class TwigRendererService
{
    public function __construct(private TwigExtensions $twigExtensions) {}

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
        $twig->addExtension($this->twigExtensions);
        $twig->addExtension(new StringLoaderExtension());
        $twig->addExtension(new StringExtension());
        $twig->addExtension(new IntlExtension());
        return $twig;
    }
}
