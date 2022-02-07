<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RedirectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('redirects')->insert([
                'blog_id' => "1",
                'old_url' => "hyvor talk",
                'new_url' => "hyvor blogs",
                'type' => "301",
            ],);
    }
}
