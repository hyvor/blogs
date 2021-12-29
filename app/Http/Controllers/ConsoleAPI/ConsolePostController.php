<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Models\Blog;
use App\Models\Post;
use App\Repositories\Post\PostRepositoryInterface;
use Illuminate\Http\Request;

class ConsolePostController {

    private $postRepo;
    public function __construct(PostRepositoryInterface $postRepo) 
    {
        $this->postRepo = $postRepo;
    }

    // get post stats by status, author, and tag
    public function getStats(Blog $blog) {
        return response()->json($this->postRepo->getPostStats($blog->id));
    }

    public function getPosts(Blog $blog) {
        return response()->json(Post::get());
    }

}