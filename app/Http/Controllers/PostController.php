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

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
            ]);
        $post = new Post();
        $post->title = $request->title;
        $post->body = $request->body;
        $post->published_at = $request->published_at;

        $post->save();
        return redirect('/home')->with('success','Post created successfully!');
    }

    public function show(Post $post)
    {
        $themeInfo = array(
            'title' => 'undefined',
        );

        $page = array(
            'title' => "Example Page T",
            'content' => "Example page content...",
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
        $themeObj = new Theme($data);
        return $themeObj->printVar($post);        
        // return view('posts.edit', compact('post'));
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
}