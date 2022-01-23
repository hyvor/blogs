<?php

namespace App\Http\Controllers\DataAPI;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domains\DataAPI\DataAPISingleRequest;
use App\Domains\Post\PostRepositoryInterface;

class DataAPIController extends Controller
{
    private $postRepo;

    public function __construct(PostRepositoryInterface $postRepo)
    {
        $this->postRepo = $postRepo;
    }

    public function post(Request $request)
    {
        $post = $this->dataAPIRepo->post($this->getSingleRequestForRepo($request));
        return response()->json($post);
    }

    public function tag(Request $request)
    {
        $tag = $this->dataAPIRepo->tag($this->getSingleRequestForRepo($request));
        return response()->json($tag);
    }

    private function getSingleRequestForRepo(Request $request)
    {
        return (new DataAPISingleRequest())
            ->setBlogId($request->attributes->get('blog'))
            ->setId($request->input('id'))
            ->setSlug($request->input('slug'))
            ->setKeys($request->input('keys'));
    }
}
