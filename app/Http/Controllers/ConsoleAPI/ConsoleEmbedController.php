<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Media\Embed\EmbedRepository;
use App\Domains\Media\Embed\Types\EmbedType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsoleEmbedController extends Controller
{
    static function getData(Request $request)
    {
        $url = $request->input('url');
        $request->validate([
            'url' => 'required|url'
        ]);
        $embed = new EmbedType(EmbedRepository::fetch($url));

        return response()->json($embed);
    }
}
