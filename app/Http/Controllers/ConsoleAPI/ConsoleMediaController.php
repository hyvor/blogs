<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Media\Embed\EmbedRepository;
use App\Domains\Media\Embed\Types\EmbedType;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsoleMediaController extends Controller {

    static function getEmbedData(Request $request) {
        $url = $request->get('url');
        $request->validate([
            'url' => 'required|url'
        ]);
        $embed = new EmbedType(EmbedRepository::fetch($url));

        return response()->json($embed);
    }

    static function upload(Request $request) {
        $file = $request->file('file');

    }

}