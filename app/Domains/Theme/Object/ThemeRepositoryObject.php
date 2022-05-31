<?php

namespace App\Domains\Theme\Object;

use App\Domains\Theme\ThemeRepository;

class ThemeRepositoryObject
{
    /**
    * @var array<array<string,mixed>>
    */
    public array $themeRequiredData = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $dd = [];


    public function themeRequiredData(
        $type, 
        $themeName
    )
    {
        $this->themeRequiredData[] = [
            'type' => $type,
            'themeName' => $themeName,
        ];

        if(sizeOf($this->themeRequiredData) === 2){
            foreach($this->themeRequiredData as $key => $value){
                if($key === 1){
                    ThemeRepository::createTheme($value);
                }
            }
        }
    }
    
    public function theme(
        $configDef,
        $config,
        $lang,
        $styles,
        $templates,
        $assets
    )
    {
        $this->dd[] = [
            'config.def' => $configDef,
            'config' => $config,
            'lang' => $lang,
            'styles' => $styles,
            'templates' => $templates,
            'assets' => $assets,
        ];

        dump($this->dd);

    }

    public function themeVersion(){}
}