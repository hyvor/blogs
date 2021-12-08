<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Theme;


class ThemesTableDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // for ($i=0; $i < 3; $i++) { 
	    	Theme::create([
	            'name' => 'Blog_default',
	        ]);
    	// }
    }
}
