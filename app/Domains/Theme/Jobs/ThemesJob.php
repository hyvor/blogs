<?php

namespace App\Domains\Theme\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Domains\Theme\Object\ThemeRepositoryObject;
// use Github\Client;
use PhpZip\ZipFile;
use App\Domains\Theme\ThemeRepository;
use Symfony\Component\Yaml\Yaml;

// part - 1
// create a job to do the saving. - done
// create a controller. - done
// create a temporary route. - done
// create a theme repository object. - done
// create the functions in the themeRepository. -done

// part - 2
// find the matching package to get the data from the github. - done
// get the github repository data using a package or manually. - done
// then get the github repo data as a zip file. - done
// convert the zip file into an array. - done
// then create an foreach loop as key => value pairs. - done

// part - 3
// exclude the unnecessary data from the array key in the foreach loop. ( repo name, .gitIgnore etc ) - done
// use the array slice method to get rid of the unnecessary data. - done
// and the filter the array data for the specific theme. - done
// then get the theme data as an array. - done
// and then pass the theme data to the repository object.

// part - 4
// need to save the version information
// then need to get the version and the theme name from the database and check whether the theme exist.
// if the theme exist then we will have to loop through the config.yaml and check whether there is any new version.
// if the theme has a new version then we will have to re-save the new version of the theme with the version in the database. 

// part - 5
// and then check whether the theme is there in the database.
// if theme is not there then saving the theme in the database as an zip file.
// if the exist then we will have to get the version for the specific the from the theme_version table.
// and then will have to to check the version of in the config.yaml from the theme repo.
// if the themes are same there ara no changes to be done.
// if theme versions are not same then will have to save the themes zip file in the database.

// part - 6
// saving the theme zip file part should be done in the themeRepository.php file.


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