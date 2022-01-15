<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Media\Embed\EmbedRepository;
use App\Domains\Media\Embed\Types\EmbedType;
use App\Domains\Media\MediaRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Media;
use Illuminate\Http\Request;

class ConsoleMediaController extends Controller {

    static function getFiles(Request $request, Blog $blog) {
        $limit = $request->input('limit');
        $offset = $request->input('offset');
        $extension = $request->input('extension');

        MediaRepository::get($blog->id, $limit, $offset, $extension);
    }

    static function uploadFile(Request $request, Blog $blog) {
        $file = $request->file('file');

        // jpg, jpeg, png, bmp, gif, svg, or webp
        /* $request->validate([
            'file' => 'image'
        ]); */

        MediaRepository::upload($blog->id, $file);
    }

    static function deleteFile(Request $request, Blog $blog) {
        $id = $request->route('id');
        MediaRepository::delete($id);
    }

}