<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Classes\Theme;

use App\Http\Controllers\ThemeBuilderController;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();       
        $data['posts'] = $posts;
        return view('posts.index', $data);
    }

    public function create(Request $request)
    {
        $data['blog'] = array(
            'id' => $request->blogid,
        );
        return view('posts.create',$data);
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
        return redirect('/blogs/'.$request->blog_id)->with('success','Post created successfully!');
    }

    public function show(Post $post)
    {
        $themeInfo = array(
            'title' => 'undefined',
        );

        $page = array(
            'title' => "Example Page T",
            'content' => "Example page content...",
            'blog_id' => $post->blog_id,
        );


        $article = array(
            'url' => '/dashboard',
            'title' => $post->title,
            'content' => $post->body,
            'img' => '',
        );

        $data = array(
            'type' => 'post',
            'article' => $article,
            'page' => $page,
            'theme' => $themeInfo,
            'date' => '2021',
        );

        $themeObj = new Theme($data);
        return $themeObj->view();
    }

    public function edit(Post $post)
    {

        $data = array(
            'type' => 'post-edit',
            'post' => $post,
        );        
        // $themeObj = new Theme($data);
        // return $themeObj->printVar($post);        
        return view('posts.edit', compact('post'));
    }

    public function update(Post $post, Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
            ]);
        $post->title = $request->title;
        $post->body = $request->body;
        $post->published_at = $request->published_at;

        $post->save();
        return redirect('/home')->with('success','Post updated successfully!');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect('/home')->with('success','Post deleted successfully!');
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