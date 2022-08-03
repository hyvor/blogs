<?php

namespace Database\Seeders;

use App\Models\Redirect;
use Illuminate\Database\Seeder;

/**
 * Seeds
 *
 * - 100 redirects
 * - 100 media
 * -
 */
class ExtraSeeder extends Seeder
{
    public function run()
    {
        Redirect::factory()->count(100)->create(['blog_id' => config('test.blog_id')]);
    }
}
