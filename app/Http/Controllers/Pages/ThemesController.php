<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ThemesController extends Controller
{
    public function handle(Request $request)
    {
        $route = $request->route('name');
        $themeName = $route ?? 'default';

        return view('landing.themes', [
            'route' => $route ? "/$route" : '',
            'themeName' => $themeName,
        ]);
    }
}
