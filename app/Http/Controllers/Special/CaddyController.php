<?php
namespace App\Http\Controllers\Special;

use App\Domains\Blog\BlogRepository;
use Illuminate\Http\Request;

class CaddyController {

    public function checkDomain(Request $request) {

        $domain = $request->input('domain');
        $blog = BlogRepository::getBlogByCustomDomain($domain);

        if (!$blog) {
            abort(500);
        }

        return response();

    }

}