<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('blogs')->insert([
            'user_id' => "1",
            'theme_id' => "1",
            'subdomain' => "http://testblog.hyvorblogs.test/",
            'name' => "testblog",
            'description' => "test",
            'icon_media_id' => "1",
            'logo_media_id' => "1",
            'website_url' => "http://testblog.hyvorblogs.test/",
            'title' => "test theme",
            'short_description' => "hello world",
            'author_id' => "1",

        ]);
    }
}
