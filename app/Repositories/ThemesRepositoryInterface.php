<?php
/**
 * Created by PhpStorm.
 * User: LENOVO
 * Date: 2021-06-30
 * Time: 11:20 AM
 */

namespace App\Repositories;


interface ThemesRepositoryInterface
{
    // To select the theme
    public function getTheme($theme_id); 

    // Selected theme pages
    public function deliverThemeData();
}


