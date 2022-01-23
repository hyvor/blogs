<?php

namespace App\Domains\Theme;

use App\Models\Theme;
use App\Models\ThemeFile;
use App\Models\BlogThemeFile;
use App\Models\Blog;
use App\Models\Post;



Class ThemeRepository
{

    public function getSingleFile(int $blogId, string $fileName, string $type) {

        

    }


    /*
    *
    * Selecting the theme from ThemeFiles table & pasting it in the BlogThemeFiles table
    *
    */
        public static function copyTheme(){

        $theme_id = 1;

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
    * Rendering the HOME page from the database
    *
    */
    public static function deliverThemeData(){

        $blogId = Blog::select('id')
            ->value('id');

        $themeFileName= BlogThemeFile::select('name','content')
            ->where('blog_id','=', $blogId)
            ->where('name','=', 'index.twig')
            ->get();
            // ->value('content');

        return $themeFileName;
    }

    /* 
    *
    * Rendering the single.twig page data from the database
    *
    */
    public static function deliverSinglePage(){

        $blogId = Blog::select('id')
            ->value('id');

        $authorData= BlogThemeFile::select('name','content')
            ->where('blog_id','=', $blogId)
            ->where('name','=', 'single.twig')
            ->get();
            // ->value('content');

        return $authorData;
    }


    /* 
    *
    * 1. Rendering the TAG page data from the database
    * 2. Checking wheather the tag page exist in the theme.
    *
    */
    public static function deliverTagData(){

        $blogId = Blog::select('id')
            ->value('id');

        $tagData= BlogThemeFile::select('name','content')
            ->where('blog_id','=', $blogId)
            ->where('name','=', 'tag.twig')
            ->get();
            // ->value('content');

        return $tagData;
    }

    // Check wheather tag file exist or not
    public static function checkTag(){

        $tagExist= BlogThemeFile::select('name')
            ->where('name','=', 'tags.twig')
            ->first();

        return $tagExist;
    }

    /* 
    *
    * 1. Rendering the AUTHOR page data from the database.
    * 2. Checking wheather the author page exist in this theme.
    *
    */
    public static function deliverAuthorData(){

        $blogId = Blog::select('id')
            ->value('id');

        $authorData= BlogThemeFile::select('name','content')
            ->where('blog_id','=', $blogId)
            ->where('name','=', 'author.twig')
            ->get();
            // ->value('content');

        return $authorData;
    }

    // Check wheather author file exist or not
    public static function checkAuthor(){

        $authorExist= BlogThemeFile::select('name')
            ->where('name','=', 'author.twig')
            ->first();

        return $authorExist;
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

    /*
    * 
    * Get the css files
    *
    */
    public static function deliverCSS(){

        $blogId = Blog::select('id')
            ->value('id');

        $cssContent= BlogThemeFile::where('type' , 'styles') 
            ->where('name' , 'index.scss')
            // ->get();
            ->first();

        return $cssContent;
    }

    /*
    * 
    * Filternig whether it is a post, page or redirect
    *
    */
    public static function isPage(){

        $getType= Post::where('is_page') 
            // ->get();
            ->first();
        return $getType;
    }

}
