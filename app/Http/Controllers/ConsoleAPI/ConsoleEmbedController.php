<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\EmbedObject;
use App\Domains\Embed\EmbedRepository;
use App\Domains\Media\Embed\Types\EmbedType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsoleEmbedController extends Controller
{
    public static function getData(Request $request)
    {
        $url = $request->input('url');
        $request->validate([
            'url' => 'required|url'
        ]);
        $embed = new EmbedObject(EmbedRepository::fetch($url));

        return response()->json($embed);
    }
}
