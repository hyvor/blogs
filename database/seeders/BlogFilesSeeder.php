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
                        {# <link rel="stylesheet" type="text/css" href="../styles/index.css"> #}
                        <style> {{ style }} </style>
                        <title>Document</title>
                    </head>
                                
                    <body> 
                        
                        <div class="hyvor">
                            <h3> Hyvor testing the scss </h3>
                        </div>
                
                        <p>
                            {{ name }} is a {{ occupation }}
                        </p>  
                           
                        <script src="../assets/script.js"></script>
                    </body>
                                
                </html>',
                'type' => "templates",
            ],

            [
                'theme_id' => "1",
                'name' => "index.scss",
                'content' => 
                '@import "header";
                 @import "body"; 
                ',
                'type' => "styles",
            ],

            [
                'theme_id' => "1",
                'name' => "body.scss",
                'content' => 
                '$bodyColor : #dde668; 
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
                
                 .hyvor{
                    color: $color;
                    margin:$margin;
                    font-size: $fontSize;
                    background-color: rgb(123, 195, 207);
                    text-align: center;
                 }
                
                 h3{
                    padding: 5px;
                 }
                ',
                'type' => "styles",
            ],

            [
                'theme_id' => "1",
                'name' => "script.js",
                'content' => "// console.log('Submiting form');
                alert('Testing the javaScript');
                ",
                'type' => "assets",
            ],

        ]);
    }
}
