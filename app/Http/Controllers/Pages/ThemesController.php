<?php

namespace App\Http\Controllers\Pages;

use App\Data\Enums\ThemeCreationTypeEnum;
use App\Domains\Theme\ThemeRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ThemesController extends Controller
{
    public function handle(Request $request)
    {
        $route = $request->route('name');
        $themeName = $route ?? 'hello';

        $themes = ThemeRepository::getAllThemesWithLatestVersions();

        $theme = $themes->firstWhere('name', $themeName);

        if (! $theme) {
            abort(404);
        }

        $originalThemes = $themes->where('type', ThemeCreationTypeEnum::ORIGINAL);
        $portedThemes = $themes->where('type', ThemeCreationTypeEnum::PORTED);

        return view('landing.themes', [
            'route' => $route ? "/$route" : '',
            'currentTheme' => $theme,
            'originalThemes' => $originalThemes,
            'portedThemes' => $portedThemes,
        ]);
    }
}
