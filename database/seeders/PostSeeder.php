<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Client\ConnectionException;


class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('posts')->insert([
            'status' => "published",
            'content' => "Tsts post one",
            'slug' => "rasif",
            'title' => "Checking",
            'description' => "testing the deletation",
            'words' => 500,
            'blog_id' => "1",
            'language_id'=>"1",
        ]);
    }
}
