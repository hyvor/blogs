<?php

namespace App\Domains\Theme;

use App\Models\Theme;
use App\Models\ThemeFile;
use App\Models\BlogThemeFile;
use App\Models\Blog;


Class ThemeRepository 
{
    /*
    *
    * Selecting the theme from ThemeFiles table & pasting it in the BlogThemeFiles table
    *
    */
    public function copyTheme($theme_id){

        $themeID = Theme::select('id')
        ->where('id','=', $theme_id)
        ->get();

        if($themeID){

            $blogId = Blog::select('id')
            ->value('id');

            // $blogId = BlogThemeFile::join('blogs', 'blogs.id', '=', 'blog_theme_files.blogs_id')
            // ->where('id','=', $theme_id)
            // ->first();

            $themeFileName= ThemeFile::select('name','content','type')
            ->where('theme_id','=', $theme_id)
            ->get();

            foreach($themeFileName as $key => $themeFile){
                BlogThemeFile::create([
                    'blog_id'=> $blogId,
                    'name'=>$themeFile->name,
                    'content'=>$themeFile->content,
                    'type'=>$themeFile->type,
                ]);
            }

            // foreach($themeFileName as $key => $themeFile){
            //     BlogThemeFile::create([
            //         'blog_id'=> $blogId,
            //         'name'=>$themeFile['name'],
            //         'content'=>$themeFile['content'],
            //         'type'=>$themeFile['type'],
            //     ]);
            // }

        }
        
        return $themeID;
    }

    
    /* 
    *
    * Rendering the home page from the database
    *
    */
    public static function deliverThemeData(){

        $blogId = Blog::select('id')
            ->value('id');

        $themeFileName= BlogThemeFile::select('name','content')
            ->where('blog_id','=', $blogId)
            // ->where('name','=', 'index.twig')
            ->get();
            // ->value('content');

        return $themeFileName;
    }

    /* 
    *
    * Rendering the assets from the database
    *
    */
    public static function deliverAssets($urlName){

        // dd($geturl);
        // $fileName = "script.js";
        $blogId = Blog::select('id')
            ->value('id');

            // $test = 'script.js';
        $assetsFileName= BlogThemeFile::where('type' , 'assets') 
        ->where('name' , $urlName)
        ->get();
        // ->first();
        // dd($assetsFileName);

        return $assetsFileName;
    }

}