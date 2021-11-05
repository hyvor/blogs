<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Blog extends Model
{
    use HasFactory;

    public static function blogsWithThemeName() {
        $blogs = DB::table('blogs')
                    ->join('themes', 'themes.id', '=', 'blogs.theme_id')
                    ->select('blogs.*', 'themes.title as theme_name')
                    ->get();
        return $blogs;
    }


}
