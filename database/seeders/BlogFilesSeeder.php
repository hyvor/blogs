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
                'content' => '<!DOCTYPE html>
                <html lang="en">
                                
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <style>{{style}}</style>
                        <title>{{Title}}</title>
                    </head>
                                 
                    <body>       
                            <ul>
                                <li>{{ name }}</li>
                                <li>{{ number }}</li>
                                <li>{{ test }}</li>
                            </ul>
                            
                        <script>{{script | raw}}</script>
                    </body>
                                
                </html>',
                'type' => "templates",
            ],

            [
                'theme_id' => "1",
                'name' => "index.twig",
                'content' => '<!DOCTYPE html>
                <html lang="en">
                                
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <link href="{{ (style.css) |assets }}" rel="stylesheet" />                
                        
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
                        <title>Document</title>
                    </head>
                                
                    <body> 
                        <div class="hyvor">
                            <h3> assets testing section </h3>
                            {{ (style.css)  |assets }} <br>
                            {{ (script.js)  |assets }} <br>
                            {{ (hello)  |assets }}<br>
                            {{ (1.png)  |assets }}
                        </div> 
                        <div class="language">
                            <h5> language testing section </h5>
                            {{ PUBLISHED|lang }} <br>
                            {{ HEADDING|lang }} <br>
                            {{ HEADDING|lang }} <br>
                            {{ SUBHEADDING|lang }} <br>
                        </div> 
                        <div class = "tags">
                            <h5> tags testing section </h5>
                           {% hyvorTag name = "ok ok" %}
                           {{ name }}
                           {# {% endhyvorTag %} #}
                        </div>
                        <div class= "functions">
                            <h5> functions testing section </h5>
                            {% set _post = filterObject(endpoint = "post" , filter = 1) %}
                            {% set _author = filterObject(endpoint = "author") %}
                
                            {{_post}} 
                            <br>
                            {{_author}}
                        </div>
                        <div class="alert alert-primary" role="alert">
                            A simple primary alert—check it out!
                        </div>
                        {% block content %}
                        <p>
                            {{ name }} is a {{ occupation }}
                        </p>  
                        {% endblock %}
                        {# <img src="{{ (1.png) |assets }}" alt="Girl in a jacket" width="500" height="600"> #}
                        <img src="{{ (2.png) |assets }}" alt="Girl in a jacket" width="500" height="600">
                        {% block javascript %}
                            <script src=" {{ (script.js) |assets }} "></script>
                        {% endblock %}
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
