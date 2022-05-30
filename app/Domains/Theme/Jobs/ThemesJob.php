<?php

namespace App\Domains\Theme\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Domains\Theme\Object\ThemeRepositoryObject;
use Github\Client;
use PhpZip\ZipFile;
use App\Domains\Theme\ThemeRepository;

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

    public array $configDef = [];
    public array $config = [];
    public array $lang = [];
    public array $styles = [];
    public array $templates = [];
    public array $assets = [];

    public function themes()
    {
        $repo = new ThemeRepositoryObject();
        $client = new Client();
        $zipFile = new ZipFile();

        $format = 'zipball';
        $reference = 'main';

        $archive = $client->api('repo')->contents()->archive('hyvor', 'hyvor-blogs-themes', $format, $reference);
        // file_put_contents('testGitHub.zip' , $archive);

        // $zipball = "https://github.com/hyvor/hyvor-blogs-themes/zipball/main";
        // $zip = file_get_contents($zipball);

        $zipFile->openFromString($archive);

        foreach($zipFile as $entryName => $contents){

            $splitRepoName = explode("hyvor-hyvor-blogs-themes-5099764/", $entryName);
            $split = explode('/', $splitRepoName[1]);

            // if($split[0] === 'original'){
            //     $repo->themeRequiredData(
            //         type: $split[0],
            //         themeName: $split[1]
            //     );
            // }

            // if($split[0] === 'ported'){
            //     $repo->themeRequiredData(
            //         type: $split[0], 
            //         themeName: $split[1]
            //     );
            // }

            if($split[0] === 'original' || $split[0] === 'ported'){

                $repo->themeRequiredData(
                    type: $split[0],
                    themeName: $split[1]
                );

                if($split[1] != null){
                    $themeName = ThemeRepository::getThemeName($split[1]);
                    if($themeName){
                        if($split[2] != null){

                            // $this->themeFiles[$split[2]] = [$contents];
                            // dump($split[2]);
                            // $this->themeFiles = [
                            //     $split[2]=> $contents
                            // ];

                            // if($split[2] === 'config.def.yaml' || $split[2] === 'config.yaml'){
                            //     $this->themeFiles = [
                            //         $split[2] => $contents,
                            //     ];
                            // }

                            if($split[2] === 'config.def.yaml'){
                                $this->configDef = [
                                    $split[2]=> $contents
                                ];
                            }

                            if($split[2] === 'config.yaml'){
                                preg_match_all('#theme_version=([^\s]+)#', $contents, $matches);
                                dump(implode(' ', $matches[1]));

                                // $obj = unserialize($contents) ;
                                // dd(gettype($contents));

                                dd(explode(' ', $contents));

                                $this->config = [
                                    $split[2]=> $contents
                                ];
                            }

                            if($split[2] === 'lang'){
                                if($split[3] != null){
                                    $this->lang[$split[3]] = [$contents];
                                }                                
                            }

                            if($split[2] === 'styles'){
                                if($split[3] != null){
                                    $this->styles[$split[3]] = [$contents];
                                }                                
                            }

                            if($split[2] === 'templates'){
                                if($split[3] != null){
                                    $this->templates[$split[3]] = [$contents];
                                }                                
                            }

                            if($split[2] === 'assets'){
                                if($split[3] != null){
                                    $this->assets[$split[3]] = [$contents];
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
    }
}