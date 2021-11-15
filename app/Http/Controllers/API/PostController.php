<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;
use Project\Themer\Themer;
use Project\Util\Ajax;

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
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
            ]);

        $blog_id = $request->blog_id;
        $suggestSlug = Str::slug($request->title);
        $checkSlug = false;

        $checkSlug = $this->checkSlugExists($blog_id, $suggestSlug);

        if( $checkSlug->getData()->status === true ) {
            return Ajax::error('slug pattern exists.');
        } else {
            $post = new Post();
            $post->slug = $suggestSlug;
            $post->title = $request->title;
            $post->blog_id = $request->blog_id;
            $post->body = $request->body;
            $post->published_at = $request->published_at;

            $post->save();

            return Ajax::success(array(
                "post" => $post,
                "message" => "Post created successfully",
            ));
        }
        
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

    public function getSlugAvailability (Request $request, $slug = null) {
        if( $slug != null ){
            $blog_id = $request->query('blogId') ?? 0;
            $slug = $slug;
            $checked = $this->checkSlugExists($blog_id,$slug);            
            return $checked->getData()->status;
        } else {
            return false;
        }
    }


    public function checkSlugExists($blog_id = null, $slug = null)
    {
        if( Post::where( 'slug','=', $slug )
            ->where( 'blog_id','=', $blog_id )
            ->exists() ) {
            return Ajax::success();
        } else {
            return Ajax::error('slug pattern exists.');
        }
    }



    public function postsHtml($blog_id)
    {                                                                   
        $posts = Post::where('blog_id', '=', $blog_id)->get(); 
        $html = '<ul>';
        foreach($posts as $post){
            $html .= "<li><a href='/api/". $post->slug ."' class='btn btn-primary'>". $post->title ."</a></li>";
        }  
        $html .= "</ul>";
        return $html;
    }

    public function renderedThemedPost(Post $post)
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
            'url' => '/'.$post->slug,
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

        $themeObj = new Themer($data);
        return $themeObj->view();        
    }

    public function loadPost($name,$slug)
    {                                                                   
        $post = Post::where('slug', '=', $slug)->first();
        $renderedPost = $this->renderedThemedPost($post);

        return array(
                'success' => true,
                'data' => $post,
                'payload' => $renderedPost,
                // 'user' => Auth::user(),
        );
    }
    



}