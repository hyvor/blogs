<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BlogFilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('theme_files')->insert([

            [
                'theme_id' => "1",
                'name' => "single.twig",
                'content' => '',
                'type' => "templates",
            ],

            [
                'theme_id' => "1",
                'name' => "index.twig",
                'content' => '',
                'type' => "templates",
            ],

            [
                'theme_id' => "1",
                'name' => "author.twig",
                'content' => '<!DOCTYPE html>
                <html lang="en">
                                
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
                        <title>Document</title>
                    </head>
                                
                    <body> 
                        <div class="hyvor">
                            <h3> This is the author twig file</h3>
                            style.css <br>
                            script.js <br>
                            hello <br>
                            1.png
                        </div> 
                    </body>
                                
                </html>',
                'type' => "templates",
            ],

            [
                'theme_id' => "1",
                'name' => "tags.twig",
                'content' => '<!DOCTYPE html>
                <html lang="en">
                                
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
                        <title>Document</title>
                    </head>
                                
                    <body> 
                        <div class="language">
                            <h5> this is the tag twig file </h5>
                                PUBLISHED<br>
                                HEADDING<br>
                                HEADDING<br> 
                                SUBHEADDING<br>
                        </div> 
                    </body>
                                
                </html>',
                'type' => "templates",
            ],

            [
                'theme_id' => "1",
                'name' => "index.scss",
                'content' => 
                '
                @import "header";
                @import "body"; 
                ',
                'type' => "styles",
            ],

            [
                'theme_id' => "1",
                'name' => "body.scss",
                'content' => 
                '$$bodyColor : #dde668; 
                $anotherColor : #33fd0f; 
                
                body {
                    background-color:$bodyColor ;
                }
                p { 
                    font-size: 120%; 
                    color: rgb(3, 148, 51); 
                    text-align: center;
                }
                                
                ul { 
                    background-color: $anotherColor;
                }
                ',
                'type' => "styles",
            ],

            [
                'theme_id' => "1",
                'name' => "head.scss",
                'content' => 
                '$margin: 20px;
                $fontSize: 30px;
                $color: rgb(0, 255, 42); 
                $fontType:url(assets/regular.woff2);
                
                .hyvor{
                    color: $color;
                    margin:$margin;
                    font-size: $fontSize;
                    background-color: rgb(28, 97, 109);
                    text-align: center;
                }
                h3{
                    padding: 5px;
                }
                .language{
                    color: $color;
                    background-color: rgb(26, 15, 15);
                    margin: 20px;
                    text-align: center;
                    padding: 10px;
                }
                
                .tags{
                    color: $color;
                    background-color: rgb(141, 75, 75);
                    margin: 20px;
                    text-align: center;
                    padding: 10px; 
                }
                
                .functions{
                    color: $color;
                    background-color: rgb(75, 82, 141);
                    margin: 20px;
                    text-align: center;
                    padding: 10px; 
                }
                ',
                'type' => "styles",
            ],

            [
                'theme_id' => "1",
                'name' => "script.js",
                'content' => "console.log('Submiting form');
                // alert('Testing the javaScript');
                ",
                'type' => "assets",
            ],

        ]);
    }
}
