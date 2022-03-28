<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('navigations')->insert(
            [
                'blog_id' => "1",
                'name' => "hyvor talk",
                'url' => "hyvor blogs",
                'type' => "header",
            ],
        );
    }
}
