<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Blog;
use App\Models\Navigation;
use App\Models\Redirect;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->blog = Blog::find(config('test.blog_id'));

        Navigation::factory()
            ->count(8)
            ->create([
                'blog_id' => $this->blog,
            ]);

        Redirect::factory()
            ->count(10)
            ->create([
                'blog_id' => $this->blog,
            ]);
    }
}
