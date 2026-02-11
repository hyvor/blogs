<?php

namespace App\Http\Controllers\CliAPI;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CliAPIController
{
    public function updateFiles(Request $request, Blog $blog) : JsonResponse
    {
        $files = (array) $request->input('files');
        $reset = (bool) $request->input('reset');

        if ($reset) {
            ThemeFilesRepository::deleteAllFiles($blog);
        }

        foreach ($files as $path => $content) {
            $path = trim($path, '/');
            $split = explode('/', $path);

            $file = $split[1] ?? $split[0];
            $folder = ThemeFileFolderEnum::tryFrom(isset($split[1]) ? $split[0] : '');

            if ($file) {
                ThemeFilesRepository::createOrUpdateFile(
                    $blog,
                    $folder,
                    $file,
                    base64_decode($content)
                );
            }
        }

        return response()->json();
    }

    /*public function delivery(Request $request, Blog $blog)
    {
        $path = $request->input('path');
        $host = $request->input('host');

        $blog->hosting_at = BlogHostingAtEnum::SELF;
        $blog->hosting_url = 'http://' . $host;
        $blog->save();

        $response = DeliveryRepository::getResponseObject($blog, $path);

        if (isset($response->content)) {
            $response->content = base64_encode($response->content);
        }

        return response()->json($response);
    }*/
}
