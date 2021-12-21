<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsoleViewController extends Controller {

    public function __invoke(Request $request) {

        $customConsole = null;
        if (!$request->is('console/*')) {
            /**
             * This is a request from a subdomain
             * that is set up for custom console (Enterprise Plan)
             */
            $customConsole = []; // data
        }

        $userBlogs = null;

        $config = [
            'customConsole' => $customConsole,

            'blogs' => $userBlogs
        ];

        return view('console');

    }

}