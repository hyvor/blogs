<?php

namespace App\Http\Controllers\DataAPI;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\DataAPI\DataAPISingleRequest;
use App\Repositories\DataAPI\DataAPIResponseSuccess;
use App\Repositories\DataAPI\DataAPIRepositoryInterface;


class DataAPIController extends Controller
{

    private $dataAPIRepo;

    public function __construct(DataAPIRepositoryInterface $dataAPIRepo) {
        $this->dataAPIRepo = $dataAPIRepo;
    }

    public function post(Request $request) {
        $post = $this->dataAPIRepo->post($this->getSingleRequestForRepo($request));
        // return response()->json($post);
        return 'hello world';
    }

    public function tag(Request $request) {
        $tag = $this->dataAPIRepo->tag($this->getSingleRequestForRepo($request));
        return response()->json($tag);
    }

    private function getSingleRequestForRepo(Request $request) {
        return (new DataAPISingleRequest())
            ->setBlogId($request->attributes->get('blog'))
            ->setId($request->input('id'))
            ->setSlug($request->input('slug'))
            ->setKeys($request->input('keys'));
    }

}
