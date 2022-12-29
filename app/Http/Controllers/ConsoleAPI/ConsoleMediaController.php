<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Media\MediaObject;
use App\Data\Objects\ConsoleAPI\Media\UnsplashImageObject;
use App\Domains\Media\MediaRepository;
use App\Domains\Media\Services\UnsplashService;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsoleMediaController extends Controller
{
    public static function getMedia(Request $request, Blog $blog)
    {
        $request->validate([
            'limit' => 'integer',
            'offset' => 'integer',
            'extension' => 'string',
        ]);

        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);
        $extension = $request->input('extension');

        $media = MediaRepository::get($blog, $limit, $offset, $extension)->mapInto(MediaObject::class);

        return response()->json($media);
    }

    public static function uploadFile(Request $request, Blog $blog)
    {
        $request->validate([
            'file' => 'required|file|max:'.config('limits.max_media_upload_size_kb'),
            'post_id' => 'integer'
        ]);
        $file = $request->file('file');
        $postId = $request->input('post_id');

        $media = MediaRepository::upload($blog, $file, $postId);

        return response()->json(new MediaObject($media));
    }

    public function uploadFileFromUrl(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'url' => 'required|url',
            'post_id' => 'integer|nullable'
        ]);

        $url = $request->string('url');
        $postId = $request->has('post_id') ? $request->integer('post_id') : null;

        $media = (new MediaRepository)->uploadFromUrl($blog, $url, $postId);

        return response()->json(new MediaObject($media));

    }

    public static function deleteFile(Media $media)
    {
        MediaRepository::delete($media);
    }

    public static function searchUnsplash(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
            'page' => 'required|integer',
        ]);

        $search = $request->input('search');
        $page = (int) $request->input('page');

        $unsplash = app(UnsplashService::class);
        $images = $unsplash->search($search, $page)->mapInto(UnsplashImageObject::class);

        return response()->json($images);
    }
}
