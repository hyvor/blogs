<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\DeliveryAPI\DeliveryAPIRepositoryInterface;
use App\Models\ThemeFile;

class ThemesController extends Controller
{
    private $themeRepo;

    public function __construct(DeliveryAPIRepositoryInterface $themeRepository)
    {
        $this->themeRepo = $themeRepository;
    }


    // Bloger Theme Select
    public function selectTheme(){

        // dd('hello world');
        $theme_id = 1;
        $this->themeRepo->copyTheme($theme_id);

        $selectedTheme = $this->themeRepo->copyTheme($theme_id);
        return view('themes.select_theme');
    }
}
