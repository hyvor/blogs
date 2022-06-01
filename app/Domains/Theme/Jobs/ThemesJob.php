<?php

namespace App\Domains\Theme\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Domains\Theme\Object\ThemeRepositoryObject;
// use Github\Client;
use PhpZip\ZipFile;
use App\Domains\Theme\ThemeRepository;
use Symfony\Component\Yaml\Yaml;


class ThemesJob implements ShouldQueue
{
    /**
    * @var array<array<string,mixed>>
    */
    public array $themeFiles = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $configDef = [];
    
    /**
    * @var array<array<string,mixed>>
    */
    public array $config = [];
    
    /**
    * @var array<array<string,mixed>>
    */
    public array $lang = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $styles = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $templates = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $assets = [];

    public function themes()
    {
        $repo = new ThemeRepositoryObject();
        $zipFile = new ZipFile();

        // $client = new Client();
        // $format = 'zipball';
        // $reference = 'main';
        // $archive = $client->api('repo')->contents()->archive('hyvor', 'hyvor-blogs-themes', $format, $reference);

        $zipball = "https://github.com/hyvor/hyvor-blogs-themes/zipball/main";
        $zip = file_get_contents($zipball);

        $zipFile->openFromString($zip);


        foreach($zipFile as $entryName => $contents){

            $split = explode('/', $entryName);
            $folder = $split[1]; // original or ported

            if($folder === 'original' || $folder === 'ported'){

                $themeFolderName = $split[2]; // theme folder name ex: default

                $repo->themeRequiredData(
                    type: $folder,
                    themeName: $themeFolderName
                );

                if($themeFolderName != null){
                    $themeName = ThemeRepository::getThemeName($themeFolderName);
                    if($themeName){

                        $folderOrFIleName = $split[3]; // folders or file name ex: lang, config.yaml

                        if($folderOrFIleName != null){

                            if($folderOrFIleName === 'config.def.yaml'){
                                $this->configDef = [
                                    $folderOrFIleName=> $contents
                                ];
                            }

                            if($folderOrFIleName === 'config.yaml'){
                                $yaml = Yaml::parse($contents);
                                ThemeRepository::createThemeVersion($yaml);
                                $this->config = [
                                    $folderOrFIleName=> $contents
                                ];
                            }

                            if($folderOrFIleName === 'lang'){
                                $subFileName = $split[4]; // nested file names
                                if($subFileName !== null){
                                    $this->lang[$subFileName] = [$contents];
                                }                                
                            }

                            if($folderOrFIleName === 'styles'){
                                $subFileName = $split[4];
                                if($subFileName !== null){
                                    $this->styles[$subFileName] = [$contents];
                                }                                
                            }

                            if($folderOrFIleName === 'templates'){
                                $subFileName = $split[4];
                                if($subFileName !== null){
                                    $this->templates[$subFileName] = [$contents];
                                }                                
                            }

                            if($folderOrFIleName === 'assets'){
                                $subFileName = $split[4];
                                if($subFileName !== null){
                                    $this->assets[$subFileName] = [$contents];
                                }                                
                            }
                        }
    
                    }
                }

            }

        }

        $this->themeFiles[] = [
            'config.def' => $this->configDef,
            'config' => $this->config,
            'lang' => $this->lang,
            'styles' => $this->styles,
            'templates' => $this->templates,
            'assets' => $this->assets,
        ];


        dump($this->themeFiles);

       
        // dd(implode(" ",$this->themeFiles));

        // $convertToString = $zipFile->addAll($this->themeFiles);

        // dd($convertToString);

        // dump($this->themeFiles);

        // $repo->theme(
        //     themeArray: $this->themeFIles,
        // );
    }
}