<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tags')->insert([
            'blog_id' => "1",
            'name' => "testTag",
            'slug' => "http://testblog.hyvorblogs.test/tag/testTag",
            'description' => "hello world",
            'feature_image_media_id' => "1",
        ]);
    } 
}
