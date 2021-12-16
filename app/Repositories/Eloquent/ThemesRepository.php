<?php

namespace App\Repositories\Eloquent;
use App\Repositories\ThemesRepositoryInterface;
use App\Models\Theme;
use App\Models\ThemeFile;
use App\Models\BlogThemeFile;
use App\Models\Blog;


Class ThemesRepository implements ThemesRepositoryInterface
{

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

    
    // Home page of the blog
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

}