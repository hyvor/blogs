<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;
use PhpZip\ZipFile;

class ThemeVersionFactory extends Factory
{

    public function definition()
    {

        $zip = new ZipFile();
        $themeName = $this->faker->name();
        $themeVersion = rand() . "." . rand();
        $zip->addFromString('config.yaml', "THEME_NAME=$themeName\nTHEME_VERSION=$themeVersion");
        $zip->addFromString('templates/index.twig', '{{ _lang.code }}');

        $zip = $zip->outputAsString();

        return [
            'theme_id' => Theme::factory(),
            'version' => $themeVersion,
            'zip' => $zip
        ];
    }

}