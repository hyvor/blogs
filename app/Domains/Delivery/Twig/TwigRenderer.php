<?php

namespace App\Domains\Delivery\Twig;

use Illuminate\Support\Facades\App;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

class TwigRenderer
{
    public static function renderFile(string $file, array $vars)
    {
        return self::renderString(file_get_contents($file), $vars);
    }

    public static function renderString(string $string, array $vars)
    {
        $fakeFileName = 'index.twig';

        $loader = new ArrayLoader([
            $fakeFileName => $string,
        ]);

        $twig = self::getEnvironment($loader);

        return $twig->render($fakeFileName, $vars);
    }

    public static function renderFromFiles(array $files, array $vars, string $fileName)
    {
        $loader = new ArrayLoader($files);

        $twig = self::getEnvironment($loader);

        return $twig->render($fileName, $vars);
    }

    private static function getEnvironment(ArrayLoader $loader)
    {

        $isLocal = App::environment('local');

        $twig = new Environment($loader, [
            'cache' => false,
            'debug' => $isLocal,
        ]);

        // hb-defined filters and functions
        $twig->addExtension(new TwigExtensions());

        // for template_from_string
        $twig->addExtension(new StringLoaderExtension());

        // string filters
        $twig->addExtension(new StringExtension());

        // format_datetime https://twig.symfony.com/doc/3.x/filters/format_datetime.html
        $twig->addExtension(new IntlExtension());

        // debugging
        if ($isLocal) {
            $twig->addExtension(new DebugExtension());
        }

        return $twig;
    }
}
