<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Media\MediaObject;
use App\Data\Objects\ConsoleAPI\Media\UnsplashImageObject;
use App\Domains\Media\MediaRepository;
use App\Domains\Media\Services\UnsplashService;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConsoleMediaController extends Controller
{

    public static function getMedia(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'limit' => 'integer',
            'offset' => 'integer',
            'search' => 'string|nullable',
            'extensions' => 'array|nullable',
            'extensions.*' => 'string',
            'type' => 'string|nullable'
        ]);

        $limit = $request->integer('limit', 50);
        $offset = $request->integer('offset', 0);

        $search = $request->has('search') ?
            (string) $request->string('search') :
            null;
            

        /** @var string[]|null $extensions */
        $extensions = $request->input('extensions');

        $type = (string) $request->string('type');

        if ($type === 'image') {
            $extensions = MediaRepository::IMAGE_EXTENSIONS;
        }

        $media = MediaRepository::get(
            $blog,
            $limit,
            $offset,
            $extensions,
            $search,
        )
            ->map(fn ($media) => new MediaObject($media, $blog));
        return response()->json($media);
    }

    public function uploadFile(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:'.config('limits.max_media_upload_size_kb'),
            'post_id' => 'integer',
            'name' => 'string|nullable'
        ]);

        if (MediaRepository::hasLimitsExceeded($blog)) {
            throw new TrustedException('Total storage limit exceeded. Please upgrade your plan.');
        }

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $request->file('file');
        $postId = $request->has('post_id') ? $request->integer('post_id') : null;
        $fileName = $request->has('name') ? $request->input('name') : null;

        if ($fileName) {
            $this->validateFilename($fileName);
        }
   
        $media = MediaRepository::upload($blog, $file, $postId, $fileName);

        return response()->json(new MediaObject($media, $blog));
    }

    public function uploadFileFromUrl(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'url' => 'required|url',
            'post_id' => 'integer|nullable'
        ]);

        if (MediaRepository::hasLimitsExceeded($blog)) {
            throw new TrustedException('Total storage limit exceeded. Please upgrade your plan.');
        }

        $url = (string) $request->string('url');
        $postId = $request->has('post_id') ? $request->integer('post_id') : null;

        $media = (new MediaRepository)->uploadFromUrl($blog, $url, $postId);

        return response()->json(new MediaObject($media, $blog));

    }

    public static function deleteFile(Media $media) : JsonResponse
    {
        MediaRepository::delete($media);
        return response()->json();
    }

    public static function searchUnsplash(Request $request) : JsonResponse
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

    public function updateMedia(Request $request, Blog $blog, Media $media) : JsonResponse
    {
        $request->validate([
            'name' => 'string|required',
        ]);

        $name = (string) $request->string('name');
        $name = Str::kebab($name);
        $this->validateFilename($name);

        $media = MediaRepository::updateName($media, $name, $blog);

        return response()->json(new MediaObject($media, $blog));
    }

    private function validateFilename(string $name): void
    {
        if (str_contains($name, '/')) {
            throw new TrustedException('Invalid filename: / is not allowed');
        }
    }
}
