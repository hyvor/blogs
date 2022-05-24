<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Media\MediaObject;
use App\Data\Objects\ConsoleAPI\Media\UnsplashImageObject;
use App\Domains\Media\MediaRepository;
use App\Domains\Media\UnsplashRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class ConsoleMediaController extends Controller
{
    public static function getFiles(Request $request, Blog $blog)
    {
        $limit = $request->input('limit') ?? 50;
        $offset = $request->input('offset');
        $extension = $request->input('extension');

        $request->validate([
            'limit' => 'integer',
            'offset' => 'integer',
            'extension' => 'string',
        ]);

        $media = MediaRepository::get($blog->id, $limit, $offset, $extension)
            ->map(function ($m) {
                return new MediaObject($m);
            });

        return response()->json($media);
    }

    public static function uploadFile(Request $request, Blog $blog)
    {
        $file = $request->file('file');

        $request->validate([
            'file' => 'required|file',
        ]);

        // jpg, jpeg, png, bmp, gif, svg, or webp
        /* $request->validate([
            'file' => 'required|image'
        ]); */

        $media = MediaRepository::upload($blog, $file);

        return response()->json(new MediaObject($media));
    }

    public static function deleteFile(Request $request)
    {
        $id = $request->route('id');
        MediaRepository::delete($id);
    }

    public static function searchUnsplash(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
            'page' => 'required|integer',
        ]);

        $search = $request->input('search');
        $page = (int) $request->input('page');

        $images = UnsplashRepository::search($search, $page)->map(function ($image) {
            return new UnsplashImageObject($image);
        });

        return response()->json($images);
    }
}
