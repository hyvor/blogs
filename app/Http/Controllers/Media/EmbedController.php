<?php
namespace App\Http\Controllers\Media;

use App\Domains\Media\Embed\EmbedRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmbedController extends Controller {

    /**
     * Rich content loads inside our iframe.
     */
    public function embedRichIframe(Request $request) {
        $url = $request->get('url');

        $embed = EmbedRepository::fetch($url);

        if ($embed->type !== 'rich') { // links and errors are not counted
            return ""; // empty response
        }

        return <<<HTML
        <html>
            <head>
                <style>
                    body {margin:0;}
                </style>
            </head>
            <body>           
                $embed->html;
            </body>
        </html>
        HTML;
    }

}