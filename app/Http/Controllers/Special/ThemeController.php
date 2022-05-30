<?php

namespace App\Http\Controllers\Special;

use App\Domains\Theme\Jobs\ThemesJob;
use Illuminate\Http\Request;

class ThemeController
{
    public function themes(Request $request)
    {
        dispatch(new ThemesJob());
    }
}
