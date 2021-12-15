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
                    <title>Document</title>
                </head>
                
                <body>
                
                    <p>
                        {{ name }} is a {{ occupation }}
                    </p>
                
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
                    <title>Document</title>
                </head>
                
                <body>
                
                    <p>
                        {{ name }} this is the index twig page
                    </p>
                
                </body>
                
                </html>',
                'type' => "template",
            ],

            [
                'theme_id' => "1",
                'name' => "styles.css",
                'content' => "p { font-size: 120%; color: dimgray; }
                a { text-decoration: none; }
                a:hover { text-decoration: underline; }
                ",
                'type' => "asset",
            ],

        ]);
    }
}
