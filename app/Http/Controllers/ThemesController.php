<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ThemesRepositoryInterface;
use App\Models\ThemeFile;

class ThemesController extends Controller
{
    private $themeRepo;

    public function __construct(ThemesRepositoryInterface $themeRepository)
    {
        $this->themeRepo = $themeRepository;
    }


    // Bloger Theme Select
    public function selectTheme(){

        $theme_id = 1;
        $this->themeRepo->getTheme($theme_id);

        $selectedTheme = $this->themeRepo->getTheme($theme_id);
        return view('themes.select_theme');
    }
}
