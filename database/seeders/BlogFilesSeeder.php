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
                'type' => "template",
            ],

            [
                'theme_id' => "1",
                'name' => "index.twig",
                'content' => '<!DOCTYPE html>
                <html lang="en">
                                
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <style>{{style}}</style>
                        <title>Document</title>
                    </head>
                                
                    <body>       
                        <p>
                            {{ name }} is a {{ occupation }}
                        </p>     
                        <script>{{script | raw}}</script>
                    </body>
                                
                </html>',
                'type' => "template",
            ],

            [
                'theme_id' => "1",
                'name' => "style.css",
                'content' => "body {
                    background-color:black;
                }
                p { 
                    font-size: 120%; 
                    color: rgb(3, 148, 51); 
                }
                                
                ul { 
                    background-color: burlywood;
                }
                ",
                'type' => "asset",
            ],

            [
                'theme_id' => "1",
                'name' => "script.js",
                'content' => "// console.log('Submiting form');
                alert('Testing the javaScript');
                ",
                'type' => "asset",
            ],

        ]);
    }
}
