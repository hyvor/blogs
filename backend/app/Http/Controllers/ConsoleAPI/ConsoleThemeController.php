<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Theme\ThemeObject;
use App\Domains\Theme\ThemeRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ConsoleThemeController extends Controller
{
    public function getAllThemes() : JsonResponse
    {
        $all = ThemeRepository::getAllThemesWithLatestVersions();

        return response()->json($all->mapInto(ThemeObject::class));
    }
}
