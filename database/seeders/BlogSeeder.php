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
            'subdomain' => "rasif",
            'name' => "deletation",
            'description' => "testing the deletation",
            'website_url' => "http://test.hyvorblogs.test/",
            'title' => "test theme",
            'short_description' => "sorry its not vailable",
            'author_id' => "1",
            'edited_at'=> "1",
            'deleted_at'=>"1",

        ]);
    }
}
