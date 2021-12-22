<?php

namespace App\Repositories\Eloquent;
use App\Repositories\ThemesRepositoryInterface;
use App\Models\Theme;
use App\Models\ThemeFile;
use App\Models\BlogThemeFile;
use App\Models\Blog;


Class ThemesRepository implements ThemesRepositoryInterface
{
    /* 
    *
    * Selecting the theme from ThemeFiles table & pasting it in the BlogThemeFiles table
    *
    */
    public function getTheme($theme_id){

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
    public function deliverThemeData(){

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
    public function deliverAssets($assetFile){

        // $assetFile = "script.js";
        $blogId = Blog::select('id')
            ->value('id');

        $assetsFileName= BlogThemeFile::where('type' , 'assets') 
        ->where('name' , $assetFile)
        ->get();

        return $assetsFileName;
    }

}