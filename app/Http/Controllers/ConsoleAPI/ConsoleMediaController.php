<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Media\Embed\EmbedRepository;
use App\Domains\Media\Embed\Types\EmbedType;
use App\Domains\Media\MediaRepository;
use App\Domains\Media\Types\MediaOutputType;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Media;
use Illuminate\Http\Request;

class ConsoleMediaController extends Controller {

    static function getFiles(Request $request, Blog $blog) {
        $limit = $request->input('limit') ?? 50;
        $offset = $request->input('offset');
        $extension = $request->input('extension');

        $request->validate([
            'limit' => 'integer',
            'offset' => 'integer',
            'extension' => 'string'
        ]);

        $media = MediaRepository::get($blog->id, $limit, $offset, $extension)
            ->map(function($m) {
                return new MediaOutputType($m);
            });

        return response()->json($media);
    }

    static function uploadFile(Request $request, Blog $blog) {
        $file = $request->file('file');

        $request->validate([
            'file' => 'required|file'
        ]);

        // jpg, jpeg, png, bmp, gif, svg, or webp
        /* $request->validate([
            'file' => 'required|image'
        ]); */

        $media = MediaRepository::upload($blog->id, $file);

        return response()->json( new MediaOutputType($media) );
    }

    static function deleteFile(Request $request) {
        $id = $request->route('id');
        MediaRepository::delete($id);
    }

}