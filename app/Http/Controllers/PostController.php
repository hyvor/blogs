<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
// use Theme;
// use Liquid;
// use Liquid\Template;

use App\Http\Controllers\ThemeBuilderController;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        // Theme::uses('demoone');        
        $data['posts'] = $posts; 
        // return Theme::view('posts.index', $data);
        return view('posts.index', $data); 

    }

    public function create()
    {
        // Theme::uses('demotwo');
        // return Theme::view('posts.create');
        return view('posts.create');

        // $cusHtml = "";
        // $cusHtml .= "{% if products %}";
        // $cusHtml .= "<ul id='products'>";
        // $cusHtml .= "{% for product in products %}";
        // $cusHtml .= "<li>";
        // $cusHtml .= "<h2>{{ product }}</h2>";
        // $cusHtml .= "</li>";
        // $cusHtml .= "{% endfor %}";
        // $cusHtml .= "</ul>";
        // $cusHtml .= "{% endif %}";

        // $products = ['Car','Ship','Banana','Apple','Mobile phones'];

        // $template = new Template();
        // $template->parse($cusHtml);
        // return $template->render(array('products' => $products));

        // return Liquid::view('posts.base');
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
        // Theme::uses('demoone');
        // return Theme::view('posts.show', compact('post'));
        $article = array(
            'url' => '/dashboard',
            'title' => $post->title,
            'content' => $post->body,
            'img' => '',
        );

        $themeObj = new ThemeBuilderController;
        $thmSyntax = $themeObj->loadTheme(2);

$data['post'] = $post;
        return view('posts.show', $data);

        // $template = new Template();
        // $template->parse($thmSyntax);
        // return $template->render(array(
        //     'article' => $article,
        //     'date' => '2021',
        // ));
    }

    public function edit(Post $post)
    {
        // Theme::uses('demoone');
        // return Theme::view('posts.edit', compact('post'));
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
}