<?php

namespace App\Domains\Theme\Twig\Functions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use App\Http\Controllers\DataAPI\DataAPIController;
use Illuminate\Http\Request;
// use Symfony\Component\Routing\Route;
// use Symfony\Component\Routing\Annotation\Route;
// use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route;


class TwigFunctions extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('filterObject', [$this, 'calculateArea']),
        ];
    }

    public function calculateArea($endpoint , $filter = null)
    {
        // $version = 'v0';
        $subdomain = 'test';

        // $url = 'api/data/v0'.'/'.$subdomain.'/'.$endpoint;
        $url = 'api/data/v0'.'/'.$endpoint;


        $request = Request::create($url, 'GET');
        $response = app()->handle($request);
        // $response = Route::dispatch($request);
        $decode = json_decode($response);

        // $test = (new DataAPIController())->post($request);

        // return $url;
        // return $response;
        // return $request;
        return $decode;
        // return  $url.'?'.$filter;

        /*
        *
        * Single Object Endpoint
        * post, tag, author, blog
        * A single value is been returned. In json format
        * ex:- One post data
        *
        */

        // if ($endpoint == 'post')
        // {
        //     return $url.'?'.$filter.'--hello world';
        // }
        // else if ($endpoint == 'tag')
        // {
        //     return  $url.'?'.$filter.'--this is tag end point';
        // }
        // else if ($endpoint == 'author')
        // {
        //     return  $url.'?'.$filter.'--this is author end point';
        // }
        // else if ($endpoint == 'blog')
        // {
        //     return  $url.'?'.$filter.'--this is blog end point';
        // }

        /*
        *
        * Multi Object Endpoint
        * posts, tags, authors
        * Multiple values are been returned. In json format
        * ex:- Multiple post data
        *
        */
        // else if ($endpoint == 'posts')
        // {
        //     return  $url.'?'.$filter.'--the posts endpoint';
        // }
        // else if ($endpoint == 'tags')
        // {
        //     return  $url.'?'.$filter.'--this is tag end point';
        // }
        // else if ($endpoint == 'authors')
        // {
        //     return  $url.'?'.$filter.'--this is authors end point';
        // }
        // else
        // {
        //     return 'We dont support this endpoint.';
        // }


        // $url = 'api/data/'.$version.'/'.$subdomain.'/'.$endpoint.'?'.$filter;
        // api/data/v0/{subdomain}/endpoint?filter
        // return $url;
    }
} 