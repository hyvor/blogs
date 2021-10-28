<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Project\Themer\Themer;
use App\Models\Themes;
use App\Models\Blog;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\PostController;
use App\Http\Controllers\BlogThemeCustomizerController;

use App\Http\Controllers\DashboardController;

class BlogController extends Controller
{


    public function index()
    {
        $blogs = Blog::all();
        return $blogs;
    }

    public function show(Blog $blog)
    {
        return $blog;
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
            // redirect to yvor Auth login 
            $dash = new DashboardController();
            $user = $dash->logIn();      
        }

        $user = User::firstOrCreate(
            [ 
                'email' => $user->email ],
            [
                'name'     => $user->name,
                'email'    => $user->email,
                // 'password' => Hash::make('awesome'),
                'password' => Hash::make('123456'),
            ]
        );        

        $blog = new Blog();
        $blog->website_url = $request->websiteUrl;
        $blog->title = $request->title;
        $blog->short_description = $request->description;
        $blog->author_id = $user->id;
        $blog->theme_id = $request->theme;

        $blog->save();
        $blog_id = $blog->id;

        /// Copying theme parts from original theme to, customizable theme instance.
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

        return response()->json($blog, 201);
    }

    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'websiteUrl' => 'required',
            'title' => 'required',
        ]);
        $blog->website_url = $request->websiteUrl ?? $blog->website_url;
        $blog->title = $request->title ?? $blog->title;
        $blog->short_description = $request->description ?? $blog->short_description;
        $blog->author_id = $blog->author_id;
        $blog->theme_id = $request->theme ?? $blog->theme_id;

        $blog->save();        
        return response()->json($blog, 200);
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();
        return response()->json(null, 204);
    }


    public function blogsToTheme()
    {
        $blogs = Blog::blogsWithThemeName();
        return $blogs;        
    }


    public function rederedThemedBlog(Blog $blog)
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

        $themeObj = new Themer($data);
        return $themeObj->view();
    }

}
