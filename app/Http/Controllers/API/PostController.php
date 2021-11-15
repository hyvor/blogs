<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Post;
use Project\Themer\Themer;

use App\Http\Controllers\ThemeBuilderController;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return $posts;
    }


    public function show(Post $post)
    {

        return $post;

        // $themeInfo = array(
        //     'title' => 'undefined',
        // );

        // $page = array(
        //     'title' => "Example Page T",
        //     'content' => "Example page content...",
        //     'blog_id' => $post->blog_id,
        // );


        // $article = array(
        //     'url' => '/dashboard',
        //     'title' => $post->title,
        //     'content' => $post->body,
        //     'img' => '',
        // );

        // $data = array(
        //     'type' => 'post',
        //     'article' => $article,
        //     'page' => $page,
        //     'theme' => $themeInfo,
        //     'date' => '2021',
        // );

        // $themeObj = new Theme($data);
        // return $themeObj->view();
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
            ]);
        $post = new Post();
        $post->title = $request->title;
        $post->blog_id = $request->blog_id;
        $post->body = $request->body;
        $post->published_at = $request->published_at;

        $post->save();
        return response()->json($post, 201);
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
            ]);
        $post->title = $request->title ?? $post->title;
        $post->blog_id = $post->title;
        $post->body = $request->body ?? $post->body;
        $post->save();
        return response()->json($post, 200);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(null, 204);
    }

    public function fetchPartialHtml($path,$data)
    {
        //'./partials/email-template.twig'
        $posts = Post::all();       
        $data['posts'] = $posts;
        $template = $this->view->fetch($path);
        $html = $template->render($data);
        return $html;
    }

    public function postsHtml($blog_id)
    {                                                                   
        $posts = Post::where('blog_id', '=', $blog_id)->get(); 
        $html = '<ul>';
        foreach($posts as $post){
            $html .= "<li><a href='/post/". $post->id ."' class='btn btn-primary'>". $post->title ."</a></li>";
        }  
        $html .= "</ul>";
        return $html;
    }



}