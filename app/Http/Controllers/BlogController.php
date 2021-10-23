<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Classes\Theme;
use App\Models\Themes;
use App\Models\Blog;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\PostController;
use App\Http\Controllers\BlogThemeCustomizerController;

class BlogController extends Controller
{

    public function index()
    {
        $blogs = Blog::blogsWithThemeName(); 
        $data['blogs'] = $blogs;
        return view('blogs.index', $data);        
    }

    public function create()
    {
        // if (Auth::check()) {

        $themes = Themes::all(); // available themes for blogs
        $data['themes'] = array();
        foreach($themes as $theme){
            array_push($data['themes'], array(
                'id' => $theme->id,
                'name' => $theme->title,
            ));
        } 
        return view('blogs.create',$data);

        // } else {
        //     echo "not logged in";
        // }
    }


    public function store(Request $request)
    {

        $request->validate([
            'websiteUrl' => 'required',
            'title' => 'required',
            ]);

        $user = array( "id" => 0, );

        if (Auth::check()) {
            $user = Auth::user();
        } else {
            // return view('user.register');
            $user = User::create(array(
                'name'     => 'UnknownUser',
                'username' => 'Anonymous',
                'email'    => 'unknown@anonymous.io',
                'password' => Hash::make('awesome'),
            ));        
        }

        $blog = new Blog();
        $blog->website_url = $request->websiteUrl;
        $blog->title = $request->title;
        $blog->short_description = $request->description;
        $blog->author_id = $user->id;
        $blog->theme_id = $request->theme;

        $blog->save();
        $blog_id = $blog->id;

        /// Copying theme parts fro original theme to, customizable theme instance.
        $request = new Request();
        $themes = Themes::where('id', '=', $blog->theme_id)->get();
        $data = array(
            'blog_id' => $blog_id,
            'theme' => null,
            'revision' => 1,
            'unsaved' => false,
        );
        foreach($themes as $theme){
            $data['theme'] = $theme;
            $themeInstance = new BlogThemeCustomizerController();
            $custoizerTheme = $themeInstance->store($request,$data);
        }
        

        return redirect('/blogs')->with('success','Your blog is now created!');
    }

    public function show(Blog $blog)
    {

        $themeInfo = array(
            'title' => 'undefined',
        );

        $content = "<div><p><stong>by ". $blog->author_id ."</strong></p>"; 
        $content .= "<p>".$blog->short_description."</p><br/>";
        $content .= "<a href='/post/create?blogid=".$blog->id."'>Create a post</a>";
        $content .= "<h3>List of posts</h3>";
        $postCont = new PostController();
        $ht = $postCont->postsHtml($blog->id); 
        $content .= $ht;       

        $blog = array(
            'title' => $blog->title,
            'content' => $content,
            'blog_id' => $blog->id,
        );        

        $data = array(
            'type' => 'blog',
            'page' => $blog,
            'theme' => $themeInfo,
            'date' => '2021',
        );

        $themeObj = new Theme($data);
        return $themeObj->view();
    }



    public function edit(Blog $blog)
    {

        $themes = Themes::all();
        $themesList = array();
        foreach($themes as $theme){
            array_push($themesList, array(
                'id' => $theme->id,
                'name' => $theme->title,
            ));
        } 

        $data = array(
            'type' => 'blog-edit',
            'blog' => $blog,
            'themes' => $themesList,
        ); 

        return view('blogs.edit', $data);
    }

    public function update(Blog $blog, Request $request)
    {
        $request->validate([
            'websiteUrl' => 'required',
            'title' => 'required',
        ]);
        $blog->website_url = $request->websiteUrl;
        $blog->title = $request->title;
        $blog->short_description = $request->description;
        $blog->author_id = $blog->author_id;
        $blog->theme_id = $request->theme;

        $blog->save();
        return redirect('/blogs')->with('success','Blog updated successfully!');
    }    


    public function destroy(Blog $blog)
    {
        $blog->delete();
        return redirect('/blogs')->with('success','Blog deleted successfully!');
    }

}
